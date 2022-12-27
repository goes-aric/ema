<?php
namespace App\Http\Services\Penjualan;

use Exception;
use App\Models\Akun;
use App\Models\Penjualan;
use App\Models\JurnalUmum;
use App\Models\DetailJurnalUmum;
use App\Http\Services\BaseService;
use Illuminate\Support\Facades\DB;
use App\Http\Services\Jurnal\JurnalService;
use App\Http\Resources\Penjualan\PenjualanResource;

class PenjualanService extends BaseService
{
    /* PRIVATE VARIABLE */
    private $penjualanModel;
    private $jurnalModel;
    private $detailModel;
    private $akunModel;
    private $jurnalService;
    private $carbon;

    public function __construct()
    {
        $this->penjualanModel = new Penjualan();
        $this->jurnalModel = new JurnalUmum();
        $this->detailModel = new DetailJurnalUmum();
        $this->akunModel = new Akun();
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
            $newID = $this->createNoTransaksi();

            /* IMAGE VARIABLE */
            $imageName = null;
            $imagePath = storage_path("app/public/images/");
            $imageBinary = $props->file('gambar');

            /* TRY TO UPLOAD IMAGE FIRST */
            /* DECLARE NEW IMAGE VARIABLE */
            $image = $props->file('gambar');
            $newName = 'penjualan-'.$newID.'.'. $image->getClientOriginalExtension();
            $uploadImage = $this->returnUploadFile($imagePath, $newName, $imageBinary);
            if ($uploadImage['status'] == 'success') {
                $imageName = $uploadImage['filename'];
            }

            $penjualan = $this->penjualanModel;
            $penjualan->kode_jual               = $newID;
            $penjualan->tanggal                 = $props['tanggal'];
            $penjualan->nominal                 = $props['nominal'];
            $penjualan->uraian                  = $props['uraian'];
            $penjualan->kode_akun_persediaan    = $props['kode_akun_persediaan'];
            $penjualan->kode_akun_penerimaan    = $props['kode_akun_penerimaan'];
            $penjualan->gambar                  = $imageName;
            $penjualan->kode_user               = $this->returnAuthUser()->kode_user;
            $penjualan->save();

            /* GET NO JURNAL */
            $noJurnal = $this->jurnalService->createNoJurnal();

            /* CREATE JURNAL */
            $jurnal = new $this->jurnalModel;
            $jurnal->no_jurnal          = $noJurnal;
            $jurnal->tanggal_transaksi  = $props['tanggal'];
            $jurnal->deskripsi          = $props['uraian'];
            $jurnal->sumber             = $newID;
            $jurnal->gambar             = $imageName;
            $jurnal->kode_user          = $this->returnAuthUser()->kode_user;
            $jurnal->save();

            /* PERSEDIAAN */
            $akunPersediaan = $this->akunModel::where('kode_akun', '=', $props['kode_akun_persediaan'])->first();

            $persediaan = new $this->detailModel;
            $persediaan->no_jurnal  = $jurnal['no_jurnal'];
            $persediaan->kode_akun  = $akunPersediaan->kode_akun;
            $persediaan->nama_akun  = $akunPersediaan->nama_akun;
            $persediaan->debet      = 0;
            $persediaan->kredit     = $props->nominal;
            $persediaan->save();

            /* PENERIMAAN */
            $akunPenerimaan = $this->akunModel::where('kode_akun', '=', $props['kode_akun_penerimaan'])->first();

            $penerimaan = new $this->detailModel;
            $penerimaan->no_jurnal  = $jurnal['no_jurnal'];
            $penerimaan->kode_akun  = $akunPenerimaan->kode_akun;
            $penerimaan->nama_akun  = $akunPenerimaan->nama_akun;
            $penerimaan->debet      = $props->nominal;
            $penerimaan->kredit     = 0;
            $penerimaan->save();

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
                    $newName = 'penjualan-'.$penjualan->kode_jual.'.'. $image->getClientOriginalExtension();
                    $uploadImage = $this->returnUploadFile($imagePath, $newName, $imageBinary);
                    if ($uploadImage['status'] == 'success') {
                        $imageName = $uploadImage['filename'];
                    }
                }

                /* UPDATE PENJUALAN */
                $penjualan->tanggal                 = $props['tanggal'];
                $penjualan->nominal                 = $props['nominal'];
                $penjualan->uraian                  = $props['uraian'];
                $penjualan->kode_akun_persediaan    = $props['kode_akun_persediaan'];
                $penjualan->kode_akun_penerimaan    = $props['kode_akun_penerimaan'];
                $penjualan->gambar                  = $imageName;
                $penjualan->kode_user               = $this->returnAuthUser()->kode_user;
                $penjualan->update();

                /* REMOVE PREV JURNAL */
                $this->jurnalModel::where('sumber', '=', $penjualan->kode_jual)->delete();

                /* GET NO JURNAL */
                $noJurnal = $this->jurnalService->createNoJurnal();

                /* CREATE JURNAL */
                $jurnal = new $this->jurnalModel;
                $jurnal->no_jurnal          = $noJurnal;
                $jurnal->tanggal_transaksi  = $props['tanggal'];
                $jurnal->deskripsi          = $props['uraian'];
                $jurnal->sumber             = $penjualan['kode_jual'];
                $jurnal->gambar             = $imageName;
                $jurnal->kode_user          = $this->returnAuthUser()->kode_user;
                $jurnal->save();

                /* PERSEDIAAN */
                $akunPersediaan = $this->akunModel::where('kode_akun', '=', $props['kode_akun_persediaan'])->first();

                $persediaan = new $this->detailModel;
                $persediaan->no_jurnal  = $jurnal['no_jurnal'];
                $persediaan->kode_akun  = $akunPersediaan->kode_akun;
                $persediaan->nama_akun  = $akunPersediaan->nama_akun;
                $persediaan->debet      = 0;
                $persediaan->kredit     = $props->nominal;
                $persediaan->save();

                /* PENERIMAAN */
                $akunPenerimaan = $this->akunModel::where('kode_akun', '=', $props['kode_akun_penerimaan'])->first();

                $penerimaan = new $this->detailModel;
                $penerimaan->no_jurnal  = $jurnal['no_jurnal'];
                $penerimaan->kode_akun  = $akunPenerimaan->kode_akun;
                $penerimaan->nama_akun  = $akunPenerimaan->nama_akun;
                $penerimaan->debet      = $props->nominal;
                $penerimaan->kredit     = 0;
                $penerimaan->save();

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
                $jurnal = $this->jurnalModel::where('sumber', '=', $penjualan->kode_jual)->first();

                /* DELETE DETAIL JURNAL */
                $this->detailModel::where('no_jurnal', '=', $jurnal['no_jurnal'])->delete();

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
        $maxID  = DB::select('SELECT IFNULL(RIGHT(MAX(kode_jual), 5), 0) AS maxID FROM penjualan WHERE deleted_at IS NULL AND RIGHT(LEFT(kode_jual, 7), 4) = :id', ['id' => $year]);
        $newID  = (int)$maxID[0]->maxID + 1;
        $newID  = 'PJ-'.$year.''.substr("0000000$newID", -5);

        return $newID;
    }

    /* CHARTS PENJUALAN */
    public function charts(){
        try {
            $pendapatan = [];
            $year = $this->carbon::now()->format('Y');
            $data = [];
            for ($x=1; $x <= 12; $x++) {
                $data[] = $this->penjualanModel::selectRaw("$x AS month, IFNULL(SUM(nominal), 0) AS amount, 'Rupiah' AS unit")
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
