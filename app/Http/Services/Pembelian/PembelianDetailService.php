<?php
namespace App\Http\Services\Pembelian;

use Exception;
use App\Models\PembelianDetail;
use App\Http\Services\BaseService;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\Pembelian\PembelianDetailResource;

class PembelianDetailService extends BaseService
{
    /* PRIVATE VARIABLE */
    private $detailModel;
    private $carbon;

    public function __construct()
    {
        $this->detailModel = new PembelianDetail();
        $this->carbon = $this->returnCarbon();
    }

    /* FETCH ALL DETAIL PEMBELIAN */
    public function fetchAll($props){
        try {
            /* GET DATA WITH FILTER AS A MODEL */
            $datas = $this->dataFilterPagination($this->model, $props, null)->where('id_pembelian', '=', $props['id_pembelian']);

            /* RETRIEVE ALL ROW, CONVERT TO ARRAY AND FORMAT AS RESOURCE */
            $datas = $datas->get();
            $detail = PembelianDetailResource::collection($datas);

            return $detail;
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /* FETCH LIMIT DETAIL PEMBELIAN */
    public function fetchLimit($props){
        /* GET DATA FOR PAGINATION AS A MODEL */
        $getAllData = $this->dataFilterPagination($this->detailModel, [], null)->where('id_pembelian', '=', $props['id_pembelian']);
        $totalData = $getAllData->count();

        /* GET DATA WITH FILTER FOR PAGINATION AS A MODEL */
        $getFilterData = $this->dataFilterPagination($this->detailModel, $props, null)->where('id_pembelian', '=', $props['id_pembelian']);
        $totalFiltered = $getFilterData->count();

        /* GET DATA WITH FILTER AS A MODEL */
        $datas = $this->dataFilter($this->detailModel, $props, null)->where('id_pembelian', '=', $props['id_pembelian']);

        /* RETRIEVE ALL ROW, CONVERT TO ARRAY AND FORMAT AS RESOURCE */
        $datas = $datas->get();
        $datas = PembelianDetailResource::collection($datas);
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

    /* FETCH DETAIL PEMBELIAN BY ID */
    public function fetchById($id){
        try {
            $detail = $this->detailModel::find($id);
            if ($detail) {
                $detail = PembelianDetailResource::make($detail);
                return $detail;
            }

            throw new Exception('Catatan tidak ditemukan!');
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /* CREATE NEW DETAIL PEMBELIAN */
    public function createDetail($props){
        /* BEGIN DB TRANSACTION */
        DB::beginTransaction();

        try {
            $detail = $this->detailModel;
            $detail->id_pembelian   = $props['id_pembelian'];
            $detail->id_barang      = $props['id_barang'];
            $detail->harga          = $props['harga'];
            $detail->qty            = $props['qty'];
            $detail->total          = $props['total'];
            $detail->save();

            /* COMMIT DB TRANSACTION */
            DB::commit();

            $detail = PembelianDetailResource::make($detail);
            return $detail;
        } catch (Exception $ex) {
            /* ROLLBACK DB TRANSACTION */
            DB::rollback();

            throw $ex;
        }
    }

    /* UPDATE DETAIL PEMBELIAN */
    public function updateDetail($props, $id){
        /* BEGIN DB TRANSACTION */
        DB::beginTransaction();

        try {
            $detail = $this->detailModel::find($id);
            if ($detail) {
                /* UPDATE DETAIL PEMBELIAN */
                $detail->id_pembelian   = $props['id_pembelian'];
                $detail->id_barang      = $props['id_barang'];
                $detail->harga          = $props['harga'];
                $detail->qty            = $props['qty'];
                $detail->total          = $props['total'];
                $detail->update();

                /* COMMIT DB TRANSACTION */
                DB::commit();

                $detail = PembelianDetailResource::make($detail);
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

    /* DESTROY DETAIL PEMBELIAN */
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

    /* DESTROY SELECTED / MULTIPLE DETAIL PEMBELIAN */
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
