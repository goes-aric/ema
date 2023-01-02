<?php
namespace App\Http\Services\Penjualan;

use Exception;
use App\Models\Akun;
use App\Models\Penjualan;
use App\Models\JurnalUmum;
use App\Models\JurnalUmumDetail;
use App\Http\Services\BaseService;
use Illuminate\Support\Facades\DB;
use App\Http\Services\Jurnal\JurnalService;
use App\Http\Resources\Penjualan\PenjualanResource;
use App\Models\Barang;
use App\Models\PenjualanDetail;

class PenjualanService extends BaseService
{
    /* PRIVATE VARIABLE */
    private $penjualanModel;
    private $penjualanDetailModel;
    private $jurnalModel;
    private $detailModel;
    private $akunModel;
    private $barangModel;
    private $jurnalService;
    private $carbon;

    public function __construct()
    {
        $this->penjualanModel = new Penjualan();
        $this->penjualanDetailModel = new PenjualanDetail();
        $this->jurnalModel = new JurnalUmum();
        $this->detailModel = new JurnalUmumDetail();
        $this->akunModel = new Akun();
        $this->barangModel = new Barang();
        $this->jurnalService = new JurnalService;
        $this->carbon = $this->returnCarbon();
    }

    /* FETCH ALL PENJUALAN */
    public function fetchAll($props){
        /* GET DATA WITH FILTER AS A MODEL */
        $datas = $this->dataFilterPagination($this->penjualanModel, $props, null);

        /* RETRIEVE ALL ROW, CONVERT TO ARRAY AND FORMAT AS RESOURCE */
        $datas = $datas->get();
        $penjualan = PenjualanResource::collection($datas);

        return $penjualan;
    }

    /* FETCH ALL PENJUALAN */
    public function fetchLimit($props){
        /* GET DATA FOR PAGINATION AS A MODEL */
        $getAllData = $this->dataFilterPagination($this->penjualanModel, [], null);
        $totalData = $getAllData->count();

        /* GET DATA WITH FILTER FOR PAGINATION AS A MODEL */
        $getFilterData = $this->dataFilterPagination($this->penjualanModel, $props, null);
        $totalFiltered = $getFilterData->count();

        /* GET DATA WITH FILTER AS A MODEL */
        $datas = $this->dataFilter($this->penjualanModel, $props, null);

        /* RETRIEVE ALL ROW, CONVERT TO ARRAY AND FORMAT AS RESOURCE */
        $datas = $datas->get();
        $datas = PenjualanResource::collection($datas);
        $penjualan = [
            "total" => $totalData,
            "total_filter" => $totalFiltered,
            "per_page" => $props['take'],
            "current_page" => $props['skip'] == 0 ? 1 : ($props['skip'] + 1),
            "last_page" => ceil($totalFiltered / $props['take']),
            "from" => $totalFiltered === 0 ? 0 : ($props['skip'] != 0 ? ($props['skip'] * $props['take']) + 1 : 1),
            "to" => $totalFiltered === 0 ? 0 : ($props['skip'] * $props['take']) + $datas->count(),
            "show" => [
                ["number" => 25, "name" => "25"], ["number" => 50, "name" => "50"], ["number" => 100, "name" => "100"]
            ],
            "data" => $datas
        ];

        return $penjualan;
    }

    /* FETCH PENJUALAN BY ID */
    public function fetchById($id){
        try {
            $penjualan = $this->penjualanModel::find($id);
            if ($penjualan) {
                $penjualan = PenjualanResource::make($penjualan);
                return $penjualan;
            }

            throw new Exception('Catatan tidak ditemukan!');
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /* CREATE NEW PENJUALAN */
    public function createPenjualan($props){
        /* BEGIN DB TRANSACTION */
        DB::beginTransaction();

        try {
            /* GENERATE NEW ID */
            $newID = $this->checkNumberExists($props['no_transaksi']) ? $this->createNoTransaksi() : $props['no_transaksi'];

            /* IMAGE VARIABLE */
            $imageName = null;
            $imagePath = storage_path("app/public/images/");
            $imageBinary = $props->file('gambar');

            /* TRY TO UPLOAD IMAGE FIRST */
            /* DECLARE NEW IMAGE VARIABLE */
            if (!empty($props->file('gambar'))) {
                $image = $props->file('gambar');
                $newName = 'penjualan-'.$newID.'.'. $image->getClientOriginalExtension();
                $uploadImage = $this->returnUploadFile($imagePath, $newName, $imageBinary);
                if ($uploadImage['status'] == 'success') {
                    $imageName = $uploadImage['filename'];
                }
            }

            $penjualan = $this->penjualanModel;
            $penjualan->no_transaksi    = $newID;
            $penjualan->tanggal         = $props['tanggal'];
            $penjualan->total           = $props['total'];
            $penjualan->diskon          = $props['diskon'];
            $penjualan->grand_total     = $props['grand_total'];
            $penjualan->gambar          = $imageName;
            $penjualan->catatan         = $props['catatan'];
            $penjualan->id_user         = $this->returnAuthUser()->id;
            $penjualan->save();

            /* REMOVE PREV DETAILS */
            $this->penjualanDetailModel::where('id_penjualan', '=', $penjualan['id'])->delete();

            /* DETAILS */
            $hargaPokokPenjualan = 0;
            foreach (json_decode($props['details']) as $item) {
                $barang = $this->barangModel->find($item->id_barang);
                $hargaPokokPenjualan += floatval($barang->harga_beli);

                $detail = new $this->penjualanDetailModel;
                $detail->id_penjualan   = $penjualan['id'];
                $detail->id_barang      = $item->id_barang;
                $detail->harga          = $item->harga;
                $detail->qty            = $item->qty;
                $detail->total          = $item->total;
                $detail->save();
            }

            /*--------------------------------------------------------------*/
            /* JURNAL PENJUALAN BARANG / PERSEDIAAN
            /*--------------------------------------------------------------*/
            /* PERKIRAAN                  DEBET             KREDIT
            /*--------------------------------------------------------------*/
            /* KAS                        XXX
            /* DISKON                     XXX
            /*      PENJUALAN                               XXX
            /* HPP                        XXX
            /*      PERSEDIAAN                              XXX
            /*--------------------------------------------------------------*/

            /* REMOVE PREV JURNAL */
            $this->jurnalModel::where('sumber', '=', $penjualan->no_transaksi)->delete();

            /* GET NO JURNAL */
            $noJurnal = $this->jurnalService->createNoJurnal();

            /* CREATE JURNAL */
            $jurnal = new $this->jurnalModel;
            $jurnal->no_jurnal          = $noJurnal;
            $jurnal->tanggal_transaksi  = $props['tanggal'];
            $jurnal->deskripsi          = $props['uraian'];
            $jurnal->sumber             = $penjualan['no_transaksi'];
            $jurnal->gambar             = $imageName;
            $jurnal->id_user            = $this->returnAuthUser()->id;
            $jurnal->save();

            /* PENERIMAAN KAS / PIUTANG */
            $akunPenerimaan = $this->akunModel::where('default', '=', 'tunai')->first();

            $penerimaan = new $this->detailModel;
            $penerimaan->id_jurnal_umum = $jurnal['id'];
            $penerimaan->id_akun        = $akunPenerimaan->id;
            $penerimaan->kode_akun      = $akunPenerimaan->kode_akun;
            $penerimaan->nama_akun      = $akunPenerimaan->nama_akun;
            $penerimaan->debet          = floatval($props->grand_total);
            $penerimaan->kredit         = 0;
            $penerimaan->save();

            /* DISKON PENJUALAN */
            $akunDiskon = $this->akunModel::where('default', '=', 'diskon penjualan')->first();

            $diskon = new $this->detailModel;
            $diskon->id_jurnal_umum = $jurnal['id'];
            $diskon->id_akun        = $akunDiskon->id;
            $diskon->kode_akun      = $akunDiskon->kode_akun;
            $diskon->nama_akun      = $akunDiskon->nama_akun;
            $diskon->debet          = floatval($props->diskon);
            $diskon->kredit         = 0;
            $diskon->save();

            /* PENJUALAN */
            $akunPenjualan = $this->akunModel::where('default', '=', 'penjualan')->first();

            $penjualanx = new $this->detailModel;
            $penjualanx->id_jurnal_umum = $jurnal['id'];
            $penjualanx->id_akun        = $akunPenjualan->id;
            $penjualanx->kode_akun      = $akunPenjualan->kode_akun;
            $penjualanx->nama_akun      = $akunPenjualan->nama_akun;
            $penjualanx->debet          = 0;
            $penjualanx->kredit         = floatval($props->total);
            $penjualanx->save();

            /* HPP */
            $akunHPP = $this->akunModel::where('default', '=', 'hpp')->first();

            $hpp = new $this->detailModel;
            $hpp->id_jurnal_umum = $jurnal['id'];
            $hpp->id_akun        = $akunHPP->id;
            $hpp->kode_akun      = $akunHPP->kode_akun;
            $hpp->nama_akun      = $akunHPP->nama_akun;
            $hpp->debet          = floatval($hargaPokokPenjualan);
            $hpp->kredit         = 0;
            $hpp->save();

            /* PERSEDIAAN */
            $akunPersediaan = $this->akunModel::where('default', '=', 'persediaan')->first();

            $persediaan = new $this->detailModel;
            $persediaan->id_jurnal_umum = $jurnal['id'];
            $persediaan->id_akun        = $akunPersediaan->id;
            $persediaan->kode_akun      = $akunPersediaan->kode_akun;
            $persediaan->nama_akun      = $akunPersediaan->nama_akun;
            $persediaan->debet          = 0;
            $persediaan->kredit         = floatval($hargaPokokPenjualan);
            $persediaan->save();

            /* COMMIT DB TRANSACTION */
            DB::commit();

            $penjualan = PenjualanResource::make($penjualan);
            return $penjualan;
        } catch (Exception $ex) {
            /* ROLLBACK DB TRANSACTION */
            DB::rollback();

            throw $ex;
        }
    }

    /* UPDATE PENJUALAN */
    public function updatePenjualan($props, $id){
        /* BEGIN DB TRANSACTION */
        DB::beginTransaction();

        try {
            $penjualan = $this->penjualanModel::find($id);
            if ($penjualan) {
                /* IMAGE VARIABLE */
                $imageName = $penjualan->gambar;
                $imagePath = storage_path("app/public/images/");
                $imageBinary = $props->file('gambar');

                /* TRY TO UPLOAD IMAGE */
                if (!empty($props->file('gambar'))) {
                    // IF CURRENT IMAGE IS NOT EMPTY, DELETE CURRENT IMAGE
                    if ($penjualan->gambar != null) {
                        $this->returnDeleteFile($imagePath, $imageName);
                    }

                    /* DECLARE NEW IMAGE VARIABLE */
                    $image = $props->file('gambar');
                    $newName = 'penjualan-'.$penjualan->no_transaksi.'.'. $image->getClientOriginalExtension();
                    $uploadImage = $this->returnUploadFile($imagePath, $newName, $imageBinary);
                    if ($uploadImage['status'] == 'success') {
                        $imageName = $uploadImage['filename'];
                    }
                }

                /* UPDATE PENJUALAN */
                $penjualan->tanggal     = $props['tanggal'];
                $penjualan->total       = $props['total'];
                $penjualan->diskon      = $props['diskon'];
                $penjualan->grand_total = $props['grand_total'];
                $penjualan->catatan     = $props['catatan'];
                $penjualan->gambar      = $imageName;
                $penjualan->id_user     = $this->returnAuthUser()->id;
                $penjualan->update();

                /* REMOVE PREV DETAILS */
                $this->penjualanDetailModel::where('id_penjualan', '=', $penjualan['id'])->delete();

                /* DETAILS */
                $hargaPokokPenjualan = 0;
                foreach (json_decode($props['details']) as $item) {
                    $barang = $this->barangModel->find($item->id_barang);
                    $hargaPokokPenjualan += floatval($barang->harga_beli);

                    $detail = new $this->penjualanDetailModel;
                    $detail->id_penjualan   = $penjualan['id'];
                    $detail->id_barang      = $item->id_barang;
                    $detail->harga          = $item->harga;
                    $detail->qty            = $item->qty;
                    $detail->total          = $item->total;
                    $detail->save();
                }

                /*--------------------------------------------------------------*/
                /* JURNAL PENJUALAN BARANG / PERSEDIAAN
                /*--------------------------------------------------------------*/
                /* PERKIRAAN                  DEBET             KREDIT
                /*--------------------------------------------------------------*/
                /* KAS                        XXX
                /* DISKON                     XXX
                /*      PENJUALAN                               XXX
                /* HPP                        XXX
                /*      PERSEDIAAN                              XXX
                /*--------------------------------------------------------------*/

                /* REMOVE PREV JURNAL */
                $this->jurnalModel::where('sumber', '=', $penjualan->no_transaksi)->delete();

                /* GET NO JURNAL */
                $noJurnal = $this->jurnalService->createNoJurnal();

                /* CREATE JURNAL */
                $jurnal = new $this->jurnalModel;
                $jurnal->no_jurnal          = $noJurnal;
                $jurnal->tanggal_transaksi  = $props['tanggal'];
                $jurnal->deskripsi          = $props['uraian'];
                $jurnal->sumber             = $penjualan['no_transaksi'];
                $jurnal->gambar             = $imageName;
                $jurnal->id_user            = $this->returnAuthUser()->id;
                $jurnal->save();

                /* PENERIMAAN KAS / PIUTANG */
                $akunPenerimaan = $this->akunModel::where('default', '=', 'tunai')->first();

                $penerimaan = new $this->detailModel;
                $penerimaan->id_jurnal_umum = $jurnal['id'];
                $penerimaan->id_akun        = $akunPenerimaan->id;
                $penerimaan->kode_akun      = $akunPenerimaan->kode_akun;
                $penerimaan->nama_akun      = $akunPenerimaan->nama_akun;
                $penerimaan->debet          = floatval($props->grand_total);
                $penerimaan->kredit         = 0;
                $penerimaan->save();

                /* DISKON PENJUALAN */
                $akunDiskon = $this->akunModel::where('default', '=', 'diskon penjualan')->first();

                $diskon = new $this->detailModel;
                $diskon->id_jurnal_umum = $jurnal['id'];
                $diskon->id_akun        = $akunDiskon->id;
                $diskon->kode_akun      = $akunDiskon->kode_akun;
                $diskon->nama_akun      = $akunDiskon->nama_akun;
                $diskon->debet          = floatval($props->diskon);
                $diskon->kredit         = 0;
                $diskon->save();

                /* PENJUALAN */
                $akunPenjualan = $this->akunModel::where('default', '=', 'penjualan')->first();

                $penjualanx = new $this->detailModel;
                $penjualanx->id_jurnal_umum = $jurnal['id'];
                $penjualanx->id_akun        = $akunPenjualan->id;
                $penjualanx->kode_akun      = $akunPenjualan->kode_akun;
                $penjualanx->nama_akun      = $akunPenjualan->nama_akun;
                $penjualanx->debet          = 0;
                $penjualanx->kredit         = floatval($props->total);
                $penjualanx->save();

                /* HPP */
                $akunHPP = $this->akunModel::where('default', '=', 'hpp')->first();

                $hpp = new $this->detailModel;
                $hpp->id_jurnal_umum = $jurnal['id'];
                $hpp->id_akun        = $akunHPP->id;
                $hpp->kode_akun      = $akunHPP->kode_akun;
                $hpp->nama_akun      = $akunHPP->nama_akun;
                $hpp->debet          = floatval($hargaPokokPenjualan);
                $hpp->kredit         = 0;
                $hpp->save();

                /* PERSEDIAAN */
                $akunPersediaan = $this->akunModel::where('default', '=', 'persediaan')->first();

                $persediaan = new $this->detailModel;
                $persediaan->id_jurnal_umum = $jurnal['id'];
                $persediaan->id_akun        = $akunPersediaan->id;
                $persediaan->kode_akun      = $akunPersediaan->kode_akun;
                $persediaan->nama_akun      = $akunPersediaan->nama_akun;
                $persediaan->debet          = 0;
                $persediaan->kredit         = floatval($hargaPokokPenjualan);
                $persediaan->save();

                /* COMMIT DB TRANSACTION */
                DB::commit();

                $penjualan = PenjualanResource::make($penjualan);
                return $penjualan;
            } else {
                throw new Exception('Catatan tidak ditemukan!');
            }
        } catch (Exception $ex) {
            /* ROLLBACK DB TRANSACTION */
            DB::rollback();

            throw $ex;
        }
    }

    /* DESTROY PENJUALAN */
    public function destroyPenjualan($id){
        try {
            $penjualan = $this->penjualanModel::find($id);
            if ($penjualan) {
                /* DELETE JURNAL UMUM */
                $jurnal = $this->jurnalModel::where('sumber', '=', $penjualan->no_transaksi)->first();

                /* DELETE DETAIL JURNAL */
                $this->detailModel::where('id_jurnal_umum', '=', $jurnal['id'])->delete();

                $jurnal->delete();
                $penjualan->delete();

                return null;
            }

            throw new Exception('Catatan tidak ditemukan!');
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /* DESTROY SELECTED / MULTIPLE PENJUALAN */
    public function destroyMultiplePenjualan($props){
        try {
            $penjualan = $this->penjualanModel::whereIn('id', $props);

            if ($penjualan->count() > 0) {
                $penjualan->delete();

                return null;
            }

            throw new Exception('Catatan tidak ditemukan!');
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /* GENERATE NO TRANSAKSI AUTOMATICALLY */
    public function createNoTransaksi(){
        $year   = $this->carbon::now()->format('Y');

        $newID  = "";
        $maxID  = DB::select('SELECT IFNULL(RIGHT(MAX(no_transaksi), 5), 0) AS maxID FROM penjualan WHERE deleted_at IS NULL AND RIGHT(LEFT(no_transaksi, 7), 4) = :id', ['id' => $year]);
        $newID  = (int)$maxID[0]->maxID + 1;
        $newID  = 'PJ-'.$year.''.substr("0000000$newID", -5);

        return $newID;
    }

    /* CHECK INV NUMBER EXISTS */
    public function checkNumberExists($number){
        $exists = $this->penjualanModel::where('no_transaksi', '=', $number)->exists();

        return $exists;
    }

    /* CHARTS PENJUALAN */
    public function charts(){
        try {
            $pendapatan = [];
            $year = $this->carbon::now()->format('Y');
            $data = [];
            for ($x=1; $x <= 12; $x++) {
                $data[] = $this->penjualanModel::selectRaw("$x AS month, IFNULL(SUM(grand_total), 0) AS amount, 'Rupiah' AS unit")
                            ->whereYear('tanggal', '=', $year)
                            ->whereMonth('tanggal', '=', $x)
                            ->first();
            }

            $pendapatan[] = [
                'name'  => $year,
                'data'  => $data
            ];
            return $pendapatan;
        } catch (Exception $ex) {
            throw $ex;
        }
    }
}
