<?php
namespace App\Http\Services\Jurnal;

use Exception;
use App\Models\Akun;
use App\Http\Services\BaseService;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\Akun\AkunResource;

class JurnalService extends BaseService
{
    /* PRIVATE VARIABLE */
    private $jurnalModel;
    private $carbon;

    public function __construct()
    {
        $this->jurnalModel = new Akun();
        $this->carbon = $this->returnCarbon();
    }

    /* FETCH ALL JURNAL */
    public function fetchLimit($props){
        /* GET DATA FOR PAGINATION AS A MODEL */
        $getAllData = $this->dataFilterPagination($this->jurnalModel, [], null);
        $totalData = $getAllData->count();

        /* GET DATA WITH FILTER FOR PAGINATION AS A MODEL */
        $getFilterData = $this->dataFilterPagination($this->jurnalModel, $props, null);
        $totalFiltered = $getFilterData->count();

        /* GET DATA WITH FILTER AS A MODEL */
        $datas = $this->dataFilter($this->jurnalModel, $props, null);

        /* RETRIEVE ALL ROW, CONVERT TO ARRAY AND FORMAT AS RESOURCE */
        $datas = $datas->get();
        $datas = AkunResource::collection($datas);
        $jurnal = [
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

        return $jurnal;
    }

    /* FETCH JURNAL BY ID */
    public function fetchById($id){
        try {
            $jurnal = $this->jurnalModel::find($id);
            if ($jurnal) {
                $jurnal = AkunResource::make($jurnal);
                return $jurnal;
            }

            throw new Exception('Catatan tidak ditemukan!');
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /* CREATE NEW JURNAL */
    public function createAkun($props){
        /* BEGIN DB TRANSACTION */
        DB::beginTransaction();

        try {
            $newID = $this->createNoJurnal();

            $jurnal = $this->jurnalModel;
            $jurnal->kode_akun    = $props['kode_akun'];
            $jurnal->nama_akun    = $props['nama_akun'];
            $jurnal->akun_utama   = $props['akun_utama'];
            $jurnal->tipe_akun    = $props['tipe_akun'];
            $jurnal->created_id   = $this->returnAuthUser()->id;
            $jurnal->save();

            /* COMMIT DB TRANSACTION */
            DB::commit();

            $jurnal = AkunResource::make($jurnal);
            return $jurnal;
        } catch (Exception $ex) {
            /* ROLLBACK DB TRANSACTION */
            DB::rollback();

            throw $ex;
        }
    }

    /* UPDATE JURNAL */
    public function updateAkun($props, $id){
        /* BEGIN DB TRANSACTION */
        DB::beginTransaction();

        try {
            $jurnal = $this->jurnalModel::find($id);
            if ($jurnal) {
                /* UPDATE JURNAL */
                $jurnal->kode_akun    = $props['kode_akun'];
                $jurnal->nama_akun    = $props['nama_akun'];
                $jurnal->akun_utama   = $props['akun_utama'];
                $jurnal->tipe_akun    = $props['tipe_akun'];
                $jurnal->updated_id   = $this->returnAuthUser()->id;
                $jurnal->update();

                /* COMMIT DB TRANSACTION */
                DB::commit();

                $jurnal = AkunResource::make($jurnal);
                return $jurnal;
            } else {
                throw new Exception('Catatan tidak ditemukan!');
            }
        } catch (Exception $ex) {
            /* ROLLBACK DB TRANSACTION */
            DB::rollback();

            throw $ex;
        }
    }

    /* DESTROY JURNAL */
    public function destroyAkun($id){
        try {
            $jurnal = $this->jurnalModel::find($id);
            if ($jurnal) {
                $jurnal->delete();

                return null;
            }

            throw new Exception('Catatan tidak ditemukan!');
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /* DESTROY SELECTED / MULTIPLE JURNAL */
    public function destroyMultipleAkun($props){
        try {
            $jurnal = $this->jurnalModel::whereIn('id', $props);

            if ($jurnal->count() > 0) {
                $jurnal->delete();

                return null;
            }

            throw new Exception('Catatan tidak ditemukan!');
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /* FETCH ALL JURNAL FOR OPTIONS */
    public function fetchDataOptions($props){
        try {
            /* GET DATA WITH FILTER AS A MODEL */
            $datas = $this->dataFilterPagination($this->jurnalModel, $props, null);

            /* RETRIEVE ALL ROW, CONVERT TO ARRAY AND FORMAT AS RESOURCE */
            $jurnal = $datas->select('id', 'kode_akun', 'nama_akun')->get();

            return $jurnal;
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /* GENERATE NO JURNAL AUTOMATICALLY */
    public function createNoJurnal(){
        $year   = $this->carbon::now()->format('Y');

        $newID  = "";
        $maxID  = DB::select('SELECT IFNULL(RIGHT(MAX(kode_user), 5), 0) AS maxID FROM users WHERE deleted_at IS NULL AND RIGHT(LEFT(kode_user, 7), 4) = :id', ['id' => $year]);
        $newID  = (int)$maxID[0]->maxID + 1;
        $newID  = 'JU-'.$year.''.substr("0000000$newID", -3);

        return $newID;
    }
}
