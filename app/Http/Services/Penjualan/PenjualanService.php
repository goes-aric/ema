<?php
namespace App\Http\Services\Penjualan;

use Exception;
use App\Models\Akun;
use App\Http\Services\BaseService;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\Akun\AkunResource;

class PenjualanService extends BaseService
{
    /* PRIVATE VARIABLE */
    private $penjualanModel;

    public function __construct()
    {
        $this->penjualanModel = new Akun();
    }

    /* FETCH ALL PEMBELIAN */
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
        $datas = AkunResource::collection($datas);
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

    /* FETCH AKUN BY ID */
    public function fetchById($id){
        try {
            $penjualan = $this->penjualanModel::find($id);
            if ($penjualan) {
                $penjualan = AkunResource::make($penjualan);
                return $penjualan;
            }

            throw new Exception('Catatan tidak ditemukan!');
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /* CREATE NEW PEMBELIAN */
    public function createAkun($props){
        /* BEGIN DB TRANSACTION */
        DB::beginTransaction();

        try {
            $penjualan = $this->penjualanModel;
            $penjualan->kode_akun    = $props['kode_akun'];
            $penjualan->nama_akun    = $props['nama_akun'];
            $penjualan->akun_utama   = $props['akun_utama'];
            $penjualan->tipe_akun    = $props['tipe_akun'];
            $penjualan->created_id   = $this->returnAuthUser()->id;
            $penjualan->save();

            /* COMMIT DB TRANSACTION */
            DB::commit();

            $penjualan = AkunResource::make($penjualan);
            return $penjualan;
        } catch (Exception $ex) {
            /* ROLLBACK DB TRANSACTION */
            DB::rollback();

            throw $ex;
        }
    }

    /* UPDATE PEMBELIAN */
    public function updateAkun($props, $id){
        /* BEGIN DB TRANSACTION */
        DB::beginTransaction();

        try {
            $penjualan = $this->penjualanModel::find($id);
            if ($penjualan) {
                /* UPDATE PEMBELIAN */
                $penjualan->kode_akun    = $props['kode_akun'];
                $penjualan->nama_akun    = $props['nama_akun'];
                $penjualan->akun_utama   = $props['akun_utama'];
                $penjualan->tipe_akun    = $props['tipe_akun'];
                $penjualan->updated_id   = $this->returnAuthUser()->id;
                $penjualan->update();

                /* COMMIT DB TRANSACTION */
                DB::commit();

                $penjualan = AkunResource::make($penjualan);
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

    /* DESTROY PEMBELIAN */
    public function destroyAkun($id){
        try {
            $penjualan = $this->penjualanModel::find($id);
            if ($penjualan) {
                $penjualan->delete();

                return null;
            }

            throw new Exception('Catatan tidak ditemukan!');
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /* DESTROY SELECTED / MULTIPLE PEMBELIAN */
    public function destroyMultipleAkun($props){
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

    /* FETCH ALL AKUN FOR OPTIONS */
    public function fetchDataOptions($props){
        try {
            /* GET DATA WITH FILTER AS A MODEL */
            $datas = $this->dataFilterPagination($this->penjualanModel, $props, null);

            /* RETRIEVE ALL ROW, CONVERT TO ARRAY AND FORMAT AS RESOURCE */
            $penjualan = $datas->select('id', 'kode_akun', 'nama_akun')->get();

            return $penjualan;
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /* GENERATE NO TRANSAKSI AUTOMATICALLY */
    public function createNoTransaksi(){
        $year   = $this->carbon::now()->format('Y');

        $newID  = "";
        $maxID  = DB::select('SELECT IFNULL(RIGHT(MAX(kode_user), 5), 0) AS maxID FROM users WHERE deleted_at IS NULL AND RIGHT(LEFT(kode_user, 7), 4) = :id', ['id' => $year]);
        $newID  = (int)$maxID[0]->maxID + 1;
        $newID  = 'PJ-'.$year.''.substr("0000000$newID", -3);

        return $newID;
    }
}
