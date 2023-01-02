<?php
namespace App\Http\Services\Pembelian;

use Exception;
use App\Models\Akun;
use App\Models\Pembelian;
use App\Models\JurnalUmum;
use App\Models\JurnalUmumDetail;
use App\Http\Services\BaseService;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\Pembelian\PembelianResource;
use App\Http\Services\Jurnal\JurnalService;
use App\Models\Barang;
use App\Models\PembelianDetail;

class PembelianService extends BaseService
{
    /* PRIVATE VARIABLE */
    private $pembelianModel;
    private $pembelianDetailModel;
    private $jurnalModel;
    private $detailModel;
    private $akunModel;
    private $barangModel;
    private $jurnalService;
    private $carbon;

    public function __construct()
    {
        $this->pembelianModel = new Pembelian();
        $this->pembelianDetailModel = new PembelianDetail();
        $this->jurnalModel = new JurnalUmum();
        $this->detailModel = new JurnalUmumDetail();
        $this->akunModel = new Akun();
        $this->barangModel = new Barang();
        $this->jurnalService = new JurnalService;
        $this->carbon = $this->returnCarbon();
    }

    /* FETCH ALL PEMBELIAN */
    public function fetchAll($props){
        /* GET DATA WITH FILTER AS A MODEL */
        $datas = $this->dataFilterPagination($this->pembelianModel, $props, null);

        /* RETRIEVE ALL ROW, CONVERT TO ARRAY AND FORMAT AS RESOURCE */
        $datas = $datas->get();
        $pembelian = PembelianResource::collection($datas);

        return $pembelian;
    }

    /* FETCH ALL PEMBELIAN */
    public function fetchLimit($props){
        /* GET DATA FOR PAGINATION AS A MODEL */
        $getAllData = $this->dataFilterPagination($this->pembelianModel, [], null);
        $totalData = $getAllData->count();

        /* GET DATA WITH FILTER FOR PAGINATION AS A MODEL */
        $getFilterData = $this->dataFilterPagination($this->pembelianModel, $props, null);
        $totalFiltered = $getFilterData->count();

        /* GET DATA WITH FILTER AS A MODEL */
        $datas = $this->dataFilter($this->pembelianModel, $props, null);

        /* RETRIEVE ALL ROW, CONVERT TO ARRAY AND FORMAT AS RESOURCE */
        $datas = $datas->get();
        $datas = PembelianResource::collection($datas);
        $pembelian = [
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

        return $pembelian;
    }

    /* FETCH PEMBELIAN BY ID */
    public function fetchById($id){
        try {
            $pembelian = $this->pembelianModel::find($id);
            if ($pembelian) {
                $pembelian = PembelianResource::make($pembelian);
                return $pembelian;
            }

            throw new Exception('Catatan tidak ditemukan!');
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /* CREATE NEW PEMBELIAN */
    public function createPembelian($props){
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
                $newName = 'pembelian-'.$newID.'.'. $image->getClientOriginalExtension();
                $uploadImage = $this->returnUploadFile($imagePath, $newName, $imageBinary);
                if ($uploadImage['status'] == 'success') {
                    $imageName = $uploadImage['filename'];
                }
            }

            $pembelian = $this->pembelianModel;
            $pembelian->no_transaksi    = $newID;
            $pembelian->tanggal         = $props['tanggal'];
            $pembelian->metode_bayar    = $props['metode_bayar'];
            $pembelian->id_supplier     = $props['supplier'];
            $pembelian->total           = $props['total'];
            $pembelian->diskon          = $props['diskon'];
            $pembelian->grand_total     = $props['grand_total'];
            $pembelian->gambar          = $imageName;
            $pembelian->catatan         = $props['catatan'];
            $pembelian->id_user         = $this->returnAuthUser()->id;
            $pembelian->save();

            /* REMOVE PREV DETAILS */
            $this->pembelianDetailModel::where('id_pembelian', '=', $pembelian['id'])->delete();

            /* DETAILS */
            foreach (json_decode($props['details']) as $item) {
                $detail = new $this->pembelianDetailModel;
                $detail->id_pembelian   = $pembelian['id'];
                $detail->id_barang      = $item->id_barang;
                $detail->harga          = $item->harga;
                $detail->qty            = $item->qty;
                $detail->total          = $item->total;
                $detail->save();

                /* UPDATE HARGA BELI & STATUS DIGUNAKAN */
                $barang = $this->barangModel::find($item->id_barang);
                $barang->harga_beli         = $item->harga;
                $barang->status_digunakan   = 1;
                $barang->update();
            }

            /*--------------------------------------------------------------*/
            /* JURNAL PEMBELIAN BARANG / PERSEDIAAN
            /*--------------------------------------------------------------*/
            /* PERKIRAAN                  DEBET             KREDIT
            /*--------------------------------------------------------------*/
            /* PERSEDIAAN                 XXX
            /* DISKON                     XXX
            /*      KAS / UTANG USAHA                       XXX
            /*--------------------------------------------------------------*/

            /* REMOVE PREV JURNAL */
            $this->jurnalModel::where('sumber', '=', $pembelian->no_transaksi)->delete();

            /* GET NO JURNAL */
            $noJurnal = $this->jurnalService->createNoJurnal();

            /* CREATE JURNAL */
            $jurnal = new $this->jurnalModel;
            $jurnal->no_jurnal          = $noJurnal;
            $jurnal->tanggal_transaksi  = $props['tanggal'];
            $jurnal->deskripsi          = $props['uraian'];
            $jurnal->sumber             = $pembelian['no_transaksi'];
            $jurnal->gambar             = $imageName;
            $jurnal->id_user            = $this->returnAuthUser()->id;
            $jurnal->save();

            /* PERSEDIAAN */
            $akunPersediaan = $this->akunModel::where('default', '=', 'persediaan')->first();

            $persediaan = new $this->detailModel;
            $persediaan->id_jurnal_umum = $jurnal['id'];
            $persediaan->id_akun        = $akunPersediaan->id;
            $persediaan->kode_akun      = $akunPersediaan->kode_akun;
            $persediaan->nama_akun      = $akunPersediaan->nama_akun;
            $persediaan->debet          = floatval($props->grand_total);
            $persediaan->kredit         = 0;
            $persediaan->save();

            /* DISKON PEMBELIAN */
            $akunDiskon = $this->akunModel::where('default', '=', 'diskon pembelian')->first();

            $diskon = new $this->detailModel;
            $diskon->id_jurnal_umum = $jurnal['id'];
            $diskon->id_akun        = $akunDiskon->id;
            $diskon->kode_akun      = $akunDiskon->kode_akun;
            $diskon->nama_akun      = $akunDiskon->nama_akun;
            $diskon->debet          = floatval($props->diskon);
            $diskon->kredit         = 0;
            $diskon->save();

            /* PEMBAYARAN */
            $tipePembayaran = $props['metode_bayar'] == 'TUNAI' ? 'tunai' : 'pembelian kredit';
            $akunPembayaran = $this->akunModel::where('default', '=', $tipePembayaran)->first();

            $pembayaran = new $this->detailModel;
            $pembayaran->id_jurnal_umum = $jurnal['id'];
            $pembayaran->id_akun        = $akunPembayaran->id;
            $pembayaran->kode_akun      = $akunPembayaran->kode_akun;
            $pembayaran->nama_akun      = $akunPembayaran->nama_akun;
            $pembayaran->debet          = 0;
            $pembayaran->kredit         = $props->grand_total;
            $pembayaran->save();

            /* COMMIT DB TRANSACTION */
            DB::commit();

            $pembelian = PembelianResource::make($pembelian);
            return $pembelian;
        } catch (Exception $ex) {
            /* ROLLBACK DB TRANSACTION */
            DB::rollback();

            throw $ex;
        }
    }

    /* UPDATE PEMBELIAN */
    public function updatePembelian($props, $id){
        /* BEGIN DB TRANSACTION */
        DB::beginTransaction();

        try {
            $pembelian = $this->pembelianModel::find($id);
            if ($pembelian) {
                /* IMAGE VARIABLE */
                $imageName = $pembelian->gambar;
                $imagePath = storage_path("app/public/images/");
                $imageBinary = $props->file('gambar');

                /* TRY TO UPLOAD IMAGE */
                if (!empty($props->file('gambar'))) {
                    // IF CURRENT IMAGE IS NOT EMPTY, DELETE CURRENT IMAGE
                    if ($pembelian->gambar != null) {
                        $this->returnDeleteFile($imagePath, $imageName);
                    }

                    /* DECLARE NEW IMAGE VARIABLE */
                    $image = $props->file('gambar');
                    $newName = 'pembelian-'.$pembelian->no_transaksi.'.'. $image->getClientOriginalExtension();
                    $uploadImage = $this->returnUploadFile($imagePath, $newName, $imageBinary);
                    if ($uploadImage['status'] == 'success') {
                        $imageName = $uploadImage['filename'];
                    }
                }

                /* UPDATE PEMBELIAN */
                $pembelian->tanggal         = $props['tanggal'];
                $pembelian->metode_bayar    = $props['metode_bayar'];
                $pembelian->id_supplier     = $props['supplier'];
                $pembelian->total           = $props['total'];
                $pembelian->diskon          = $props['diskon'];
                $pembelian->grand_total     = $props['grand_total'];
                $pembelian->catatan         = $props['catatan'];
                $pembelian->gambar          = $imageName;
                $pembelian->id_user         = $this->returnAuthUser()->id;
                $pembelian->update();

                /* REMOVE PREV DETAILS */
                $this->pembelianDetailModel::where('id_pembelian', '=', $pembelian['id'])->delete();

                /* DETAILS */
                foreach (json_decode($props['details']) as $item) {
                    $detail = new $this->pembelianDetailModel;
                    $detail->id_pembelian   = $pembelian['id'];
                    $detail->id_barang      = $item->id_barang;
                    $detail->harga          = $item->harga;
                    $detail->qty            = $item->qty;
                    $detail->total          = $item->total;
                    $detail->save();

                    /* UPDATE HARGA BELI & STATUS DIGUNAKAN */
                    $barang = $this->barangModel::find($item->id_barang);
                    $barang->harga_beli         = $item->harga;
                    $barang->status_digunakan   = 1;
                    $barang->update();
                }

                /*--------------------------------------------------------------*/
                /* JURNAL PEMBELIAN BARANG / PERSEDIAAN
                /*--------------------------------------------------------------*/
                /* PERKIRAAN                  DEBET             KREDIT
                /*--------------------------------------------------------------*/
                /* PERSEDIAAN                 XXX
                /* DISKON                     XXX
                /*      KAS / UTANG USAHA                       XXX
                /*--------------------------------------------------------------*/

                /* REMOVE PREV JURNAL */
                $this->jurnalModel::where('sumber', '=', $pembelian->no_transaksi)->delete();

                /* GET NO JURNAL */
                $noJurnal = $this->jurnalService->createNoJurnal();

                /* CREATE JURNAL */
                $jurnal = new $this->jurnalModel;
                $jurnal->no_jurnal          = $noJurnal;
                $jurnal->tanggal_transaksi  = $props['tanggal'];
                $jurnal->deskripsi          = $props['uraian'];
                $jurnal->sumber             = $pembelian['no_transaksi'];
                $jurnal->gambar             = $imageName;
                $jurnal->id_user            = $this->returnAuthUser()->id;
                $jurnal->save();

                /* PERSEDIAAN */
                $akunPersediaan = $this->akunModel::where('default', '=', 'persediaan')->first();

                $persediaan = new $this->detailModel;
                $persediaan->id_jurnal_umum = $jurnal['id'];
                $persediaan->id_akun        = $akunPersediaan->id;
                $persediaan->kode_akun      = $akunPersediaan->kode_akun;
                $persediaan->nama_akun      = $akunPersediaan->nama_akun;
                $persediaan->debet          = floatval($props->grand_total);
                $persediaan->kredit         = 0;
                $persediaan->save();

                /* DISKON PEMBELIAN */
                $akunDiskon = $this->akunModel::where('default', '=', 'diskon pembelian')->first();

                $diskon = new $this->detailModel;
                $diskon->id_jurnal_umum = $jurnal['id'];
                $diskon->id_akun        = $akunDiskon->id;
                $diskon->kode_akun      = $akunDiskon->kode_akun;
                $diskon->nama_akun      = $akunDiskon->nama_akun;
                $diskon->debet          = floatval($props->diskon);
                $diskon->kredit         = 0;
                $diskon->save();

                /* PEMBAYARAN */
                $tipePembayaran = $props['metode_bayar'] == 'TUNAI' ? 'tunai' : 'pembelian kredit';
                $akunPembayaran = $this->akunModel::where('default', '=', $tipePembayaran)->first();

                $pembayaran = new $this->detailModel;
                $pembayaran->id_jurnal_umum = $jurnal['id'];
                $pembayaran->id_akun        = $akunPembayaran->id;
                $pembayaran->kode_akun      = $akunPembayaran->kode_akun;
                $pembayaran->nama_akun      = $akunPembayaran->nama_akun;
                $pembayaran->debet          = 0;
                $pembayaran->kredit         = $props->grand_total;
                $pembayaran->save();

                /* COMMIT DB TRANSACTION */
                DB::commit();

                $pembelian = PembelianResource::make($pembelian);
                return $pembelian;
            } else {
                throw new Exception('Catatan tidak ditemukan!');
            }
        } catch (Exception $ex) {
            /* ROLLBACK DB TRANSACTION */
            DB::rollback();

            throw $ex;
        }
    }

    /* DESTROY PEMBELIAN */
    public function destroyPembelian($id){
        try {
            $pembelian = $this->pembelianModel::find($id);
            if ($pembelian) {
                /* DELETE JURNAL UMUM */
                $jurnal = $this->jurnalModel::where('sumber', '=', $pembelian->no_transaksi)->first();

                /* DELETE DETAIL JURNAL */
                $this->detailModel::where('id_jurnal_umum', '=', $jurnal['id'])->delete();

                $jurnal->delete();
                $pembelian->delete();

                return null;
            }

            throw new Exception('Catatan tidak ditemukan!');
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /* DESTROY SELECTED / MULTIPLE PEMBELIAN */
    public function destroyMultiplePembelian($props){
        try {
            $pembelian = $this->pembelianModel::whereIn('id', $props);

            if ($pembelian->count() > 0) {
                $pembelian->delete();

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
        $maxID  = DB::select('SELECT IFNULL(RIGHT(MAX(no_transaksi), 5), 0) AS maxID FROM pembelian WHERE deleted_at IS NULL AND RIGHT(LEFT(no_transaksi, 7), 4) = :id', ['id' => $year]);
        $newID  = (int)$maxID[0]->maxID + 1;
        $newID  = 'PB-'.$year.''.substr("0000000$newID", -5);

        return $newID;
    }

    /* CHECK INV NUMBER EXISTS */
    public function checkNumberExists($number){
        $exists = $this->pembelianModel::where('no_transaksi', '=', $number)->exists();

        return $exists;
    }

    /* CHARTS PEMBELIAN */
    public function charts(){
        try {
            $pendapatan = [];
            $year = $this->carbon::now()->format('Y');
            $data = [];
            for ($x=1; $x <= 12; $x++) {
                $data[] = $this->pembelianModel::selectRaw("$x AS month, IFNULL(SUM(grand_total), 0) AS amount, 'Rupiah' AS unit")
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
