<?php
namespace App\Http\Services\Penjualan;

use Exception;
use App\Models\PenjualanDetail;
use App\Http\Services\BaseService;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\Penjualan\PenjualanDetailResource;

class PenjualanDetailService extends BaseService
{
    /* PRIVATE VARIABLE */
    private $detailModel;
    private $carbon;

    public function __construct()
    {
        $this->detailModel = new PenjualanDetail();
        $this->carbon = $this->returnCarbon();
    }

    /* FETCH ALL DETAIL PENJUALAN */
    public function fetchAll($props){
        try {
            /* GET DATA WITH FILTER AS A MODEL */
            $datas = $this->dataFilterPagination($this->model, $props, null)->where('id_penjualan', '=', $props['id_penjualan']);

            /* RETRIEVE ALL ROW, CONVERT TO ARRAY AND FORMAT AS RESOURCE */
            $datas = $datas->get();
            $detail = PenjualanDetailResource::collection($datas);

            return $detail;
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /* FETCH LIMIT DETAIL PENJUALAN */
    public function fetchLimit($props){
        /* GET DATA FOR PAGINATION AS A MODEL */
        $getAllData = $this->dataFilterPagination($this->detailModel, [], null)->where('id_penjualan', '=', $props['id_penjualan']);
        $totalData = $getAllData->count();

        /* GET DATA WITH FILTER FOR PAGINATION AS A MODEL */
        $getFilterData = $this->dataFilterPagination($this->detailModel, $props, null)->where('id_penjualan', '=', $props['id_penjualan']);
        $totalFiltered = $getFilterData->count();

        /* GET DATA WITH FILTER AS A MODEL */
        $datas = $this->dataFilter($this->detailModel, $props, null)->where('id_penjualan', '=', $props['id_penjualan']);

        /* RETRIEVE ALL ROW, CONVERT TO ARRAY AND FORMAT AS RESOURCE */
        $datas = $datas->get();
        $datas = PenjualanDetailResource::collection($datas);
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

    /* FETCH DETAIL PENJUALAN BY ID */
    public function fetchById($id){
        try {
            $detail = $this->detailModel::find($id);
            if ($detail) {
                $detail = PenjualanDetailResource::make($detail);
                return $detail;
            }

            throw new Exception('Catatan tidak ditemukan!');
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /* CREATE NEW DETAIL PENJUALAN */
    public function createDetail($props){
        /* BEGIN DB TRANSACTION */
        DB::beginTransaction();

        try {
            $detail = $this->detailModel;
            $detail->id_penjualan   = $props['id_penjualan'];
            $detail->id_barang      = $props['id_barang'];
            $detail->harga          = $props['harga'];
            $detail->qty            = $props['qty'];
            $detail->total          = $props['total'];
            $detail->save();

            /* COMMIT DB TRANSACTION */
            DB::commit();

            $detail = PenjualanDetailResource::make($detail);
            return $detail;
        } catch (Exception $ex) {
            /* ROLLBACK DB TRANSACTION */
            DB::rollback();

            throw $ex;
        }
    }

    /* UPDATE DETAIL PENJUALAN */
    public function updateDetail($props, $id){
        /* BEGIN DB TRANSACTION */
        DB::beginTransaction();

        try {
            $detail = $this->detailModel::find($id);
            if ($detail) {
                /* UPDATE DETAIL PENJUALAN */
                $detail->id_penjualan   = $props['id_penjualan'];
                $detail->id_barang      = $props['id_barang'];
                $detail->harga          = $props['harga'];
                $detail->qty            = $props['qty'];
                $detail->total          = $props['total'];
                $detail->update();

                /* COMMIT DB TRANSACTION */
                DB::commit();

                $detail = PenjualanDetailResource::make($detail);
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

    /* DESTROY DETAIL PENJUALAN */
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

    /* DESTROY SELECTED / MULTIPLE DETAIL PENJUALAN */
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
