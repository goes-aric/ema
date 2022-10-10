<?php
namespace App\Http\Services\Pembelian;

use Exception;
use App\Models\Akun;
use App\Http\Services\BaseService;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\Akun\AkunResource;

class PembelianService extends BaseService
{
    /* PRIVATE VARIABLE */
    private $pembelianModel;

    public function __construct()
    {
        $this->pembelianModel = new Akun();
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
        $datas = AkunResource::collection($datas);
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
                $pembelian = AkunResource::make($pembelian);
                return $pembelian;
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
            $pembelian = $this->pembelianModel;
            $pembelian->kode_akun    = $props['kode_akun'];
            $pembelian->nama_akun    = $props['nama_akun'];
            $pembelian->akun_utama   = $props['akun_utama'];
            $pembelian->tipe_akun    = $props['tipe_akun'];
            $pembelian->created_id   = $this->returnAuthUser()->id;
            $pembelian->save();

            /* COMMIT DB TRANSACTION */
            DB::commit();

            $pembelian = AkunResource::make($pembelian);
            return $pembelian;
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
            $pembelian = $this->pembelianModel::find($id);
            if ($pembelian) {
                /* UPDATE PEMBELIAN */
                $pembelian->kode_akun    = $props['kode_akun'];
                $pembelian->nama_akun    = $props['nama_akun'];
                $pembelian->akun_utama   = $props['akun_utama'];
                $pembelian->tipe_akun    = $props['tipe_akun'];
                $pembelian->updated_id   = $this->returnAuthUser()->id;
                $pembelian->update();

                /* COMMIT DB TRANSACTION */
                DB::commit();

                $pembelian = AkunResource::make($pembelian);
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
    public function destroyAkun($id){
        try {
            $pembelian = $this->pembelianModel::find($id);
            if ($pembelian) {
                $pembelian->delete();

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

    /* FETCH ALL PEMBELIAN FOR OPTIONS */
    public function fetchDataOptions($props){
        try {
            /* GET DATA WITH FILTER AS A MODEL */
            $datas = $this->dataFilterPagination($this->pembelianModel, $props, null);

            /* RETRIEVE ALL ROW, CONVERT TO ARRAY AND FORMAT AS RESOURCE */
            $pembelian = $datas->select('id', 'kode_akun', 'nama_akun')->get();

            return $pembelian;
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
        $newID  = 'PB-'.$year.''.substr("0000000$newID", -3);

        return $newID;
    }
}
