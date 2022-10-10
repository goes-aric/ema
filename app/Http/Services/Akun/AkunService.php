<?php
namespace App\Http\Services\Akun;

use Exception;
use App\Models\Akun;
use App\Http\Services\BaseService;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\Akun\AkunResource;

class AkunService extends BaseService
{
    /* PRIVATE VARIABLE */
    private $akunModel;

    public function __construct()
    {
        $this->akunModel = new Akun();
    }

    /* FETCH ALL AKUN */
    public function fetchLimit($props){
        /* GET DATA FOR PAGINATION AS A MODEL */
        $getAllData = $this->dataFilterPagination($this->akunModel, [], null);
        $totalData = $getAllData->count();

        /* GET DATA WITH FILTER FOR PAGINATION AS A MODEL */
        $getFilterData = $this->dataFilterPagination($this->akunModel, $props, null);
        $totalFiltered = $getFilterData->count();

        /* GET DATA WITH FILTER AS A MODEL */
        $datas = $this->dataFilter($this->akunModel, $props, null);

        /* RETRIEVE ALL ROW, CONVERT TO ARRAY AND FORMAT AS RESOURCE */
        $datas = $datas->get();
        $datas = AkunResource::collection($datas);
        $akun = [
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

        return $akun;
    }

    /* FETCH AKUN BY ID */
    public function fetchById($id){
        try {
            $akun = $this->akunModel::find($id);
            if ($akun) {
                $akun = AkunResource::make($akun);
                return $akun;
            }

            throw new Exception('Catatan tidak ditemukan!');
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /* CREATE NEW AKUN */
    public function createAkun($props){
        /* BEGIN DB TRANSACTION */
        DB::beginTransaction();

        try {
            $akun = $this->akunModel;
            $akun->kode_akun    = $props['kode_akun'];
            $akun->nama_akun    = $props['nama_akun'];
            $akun->akun_utama   = $props['akun_utama'];
            $akun->tipe_akun    = $props['tipe_akun'];
            $akun->created_id   = $this->returnAuthUser()->id;
            $akun->save();

            /* COMMIT DB TRANSACTION */
            DB::commit();

            $akun = AkunResource::make($akun);
            return $akun;
        } catch (Exception $ex) {
            /* ROLLBACK DB TRANSACTION */
            DB::rollback();

            throw $ex;
        }
    }

    /* UPDATE AKUN */
    public function updateAkun($props, $id){
        /* BEGIN DB TRANSACTION */
        DB::beginTransaction();

        try {
            $akun = $this->akunModel::find($id);
            if ($akun) {
                /* UPDATE AKUN */
                $akun->kode_akun    = $props['kode_akun'];
                $akun->nama_akun    = $props['nama_akun'];
                $akun->akun_utama   = $props['akun_utama'];
                $akun->tipe_akun    = $props['tipe_akun'];
                $akun->updated_id   = $this->returnAuthUser()->id;
                $akun->update();

                /* COMMIT DB TRANSACTION */
                DB::commit();

                $akun = AkunResource::make($akun);
                return $akun;
            } else {
                throw new Exception('Catatan tidak ditemukan!');
            }
        } catch (Exception $ex) {
            /* ROLLBACK DB TRANSACTION */
            DB::rollback();

            throw $ex;
        }
    }

    /* DESTROY AKUN */
    public function destroyAkun($id){
        try {
            $akun = $this->akunModel::find($id);
            if ($akun) {
                $akun->delete();

                return null;
            }

            throw new Exception('Catatan tidak ditemukan!');
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /* DESTROY SELECTED / MULTIPLE AKUN */
    public function destroyMultipleAkun($props){
        try {
            $akun = $this->akunModel::whereIn('id', $props);

            if ($akun->count() > 0) {
                $akun->delete();

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
            $datas = $this->dataFilterPagination($this->akunModel, $props, null);

            /* RETRIEVE ALL ROW, CONVERT TO ARRAY AND FORMAT AS RESOURCE */
            $akun = $datas->select('id', 'kode_akun', 'nama_akun')->get();

            return $akun;
        } catch (Exception $ex) {
            throw $ex;
        }
    }
}
