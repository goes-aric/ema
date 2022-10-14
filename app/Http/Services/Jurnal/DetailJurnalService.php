<?php
namespace App\Http\Services\Jurnal;

use Exception;
use App\Models\DetailJurnalUmum;
use App\Http\Services\BaseService;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\Jurnal\DetailJurnalResource;

class DetailJurnalService extends BaseService
{
    /* PRIVATE VARIABLE */
    private $detailModel;
    private $carbon;

    public function __construct()
    {
        $this->detailModel = new DetailJurnalUmum();
        $this->carbon = $this->returnCarbon();
    }

    /* FETCH ALL DETAIL JURNAL */
    public function fetchLimit($props){
        /* GET DATA FOR PAGINATION AS A MODEL */
        $getAllData = $this->dataFilterPagination($this->detailModel, [], null)->where('no_jurnal', '=', $props['no_jurnal']);
        $totalData = $getAllData->count();

        /* GET DATA WITH FILTER FOR PAGINATION AS A MODEL */
        $getFilterData = $this->dataFilterPagination($this->detailModel, $props, null)->where('no_jurnal', '=', $props['no_jurnal']);
        $totalFiltered = $getFilterData->count();

        /* GET DATA WITH FILTER AS A MODEL */
        $datas = $this->dataFilter($this->detailModel, $props, null)->where('no_jurnal', '=', $props['no_jurnal']);

        /* RETRIEVE ALL ROW, CONVERT TO ARRAY AND FORMAT AS RESOURCE */
        $datas = $datas->get();
        $datas = DetailJurnalResource::collection($datas);
        $detail = [
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

        return $detail;
    }

    /* FETCH DETAIL JURNAL BY ID */
    public function fetchById($id){
        try {
            $detail = $this->detailModel::find($id);
            if ($detail) {
                $detail = DetailJurnalResource::make($detail);
                return $detail;
            }

            throw new Exception('Catatan tidak ditemukan!');
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /* CREATE NEW DETAIL JURNAL */
    public function createDetail($props){
        /* BEGIN DB TRANSACTION */
        DB::beginTransaction();

        try {
            $detail = $this->detailModel;
            $detail->no_jurnal  = $props['no_jurnal'];
            $detail->kode_akun  = $props['kode_akun'];
            $detail->nama_akun  = $props['nama_akun'];
            $detail->debet      = $props['debet'];
            $detail->kredit     = $props['kredit'];
            $detail->save();

            /* COMMIT DB TRANSACTION */
            DB::commit();

            $detail = DetailJurnalResource::make($detail);
            return $detail;
        } catch (Exception $ex) {
            /* ROLLBACK DB TRANSACTION */
            DB::rollback();

            throw $ex;
        }
    }

    /* UPDATE DETAIL JURNAL */
    public function updateDetail($props, $id){
        /* BEGIN DB TRANSACTION */
        DB::beginTransaction();

        try {
            $detail = $this->detailModel::find($id);
            if ($detail) {
                /* UPDATE DETAIL JURNAL */
                $detail->no_jurnal  = $props['no_jurnal'];
                $detail->kode_akun  = $props['kode_akun'];
                $detail->nama_akun  = $props['nama_akun'];
                $detail->debet      = $props['debet'];
                $detail->kredit     = $props['kredit'];
                $detail->update();

                /* COMMIT DB TRANSACTION */
                DB::commit();

                $detail = DetailJurnalResource::make($detail);
                return $detail;
            } else {
                throw new Exception('Catatan tidak ditemukan!');
            }
        } catch (Exception $ex) {
            /* ROLLBACK DB TRANSACTION */
            DB::rollback();

            throw $ex;
        }
    }

    /* DESTROY DETAIL JURNAL */
    public function destroyDetail($id){
        try {
            $detail = $this->detailModel::find($id);
            if ($detail) {
                $detail->delete();

                return null;
            }

            throw new Exception('Catatan tidak ditemukan!');
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /* DESTROY SELECTED / MULTIPLE DETAIL JURNAL */
    public function destroyMultipleDetail($props){
        try {
            $detail = $this->detailModel::whereIn('id', $props);

            if ($detail->count() > 0) {
                $detail->delete();

                return null;
            }

            throw new Exception('Catatan tidak ditemukan!');
        } catch (Exception $ex) {
            throw $ex;
        }
    }
}
