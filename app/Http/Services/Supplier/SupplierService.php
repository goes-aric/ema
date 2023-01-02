<?php
namespace App\Http\Services\Supplier;

use Exception;
use App\Models\Supplier;
use App\Http\Services\BaseService;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\Supplier\SupplierResource;

class SupplierService extends BaseService
{
    /* PRIVATE VARIABLE */
    private $supplierModel;

    public function __construct()
    {
        $this->supplierModel = new Supplier();
    }

    /* FETCH ALL SUPPLIER */
    public function fetchLimit($props){
        /* GET DATA FOR PAGINATION AS A MODEL */
        $getAllData = $this->dataFilterPagination($this->supplierModel, [], null);
        $totalData = $getAllData->count();

        /* GET DATA WITH FILTER FOR PAGINATION AS A MODEL */
        $getFilterData = $this->dataFilterPagination($this->supplierModel, $props, null);
        $totalFiltered = $getFilterData->count();

        /* GET DATA WITH FILTER AS A MODEL */
        $datas = $this->dataFilter($this->supplierModel, $props, null);

        /* RETRIEVE ALL ROW, CONVERT TO ARRAY AND FORMAT AS RESOURCE */
        $datas = $datas->get();
        $datas = SupplierResource::collection($datas);
        $supplier = [
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

        return $supplier;
    }

    /* FETCH SUPPLIER BY ID */
    public function fetchById($id){
        try {
            $supplier = $this->supplierModel::find($id);
            if ($supplier) {
                $supplier = SupplierResource::make($supplier);
                return $supplier;
            }

            throw new Exception('Catatan tidak ditemukan!');
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /* CREATE NEW SUPPLIER */
    public function createSupplier($props){
        /* BEGIN DB TRANSACTION */
        DB::beginTransaction();

        try {
            $supplier = $this->supplierModel;
            $supplier->nama_supplier    = $props['nama_supplier'];
            $supplier->alamat           = $props['alamat'];
            $supplier->no_telp          = $props['no_telp'];
            $supplier->id_user          = $this->returnAuthUser()->id;
            $supplier->save();

            /* COMMIT DB TRANSACTION */
            DB::commit();

            $supplier = SupplierResource::make($supplier);
            return $supplier;
        } catch (Exception $ex) {
            /* ROLLBACK DB TRANSACTION */
            DB::rollback();

            throw $ex;
        }
    }

    /* UPDATE SUPPLIER */
    public function updateSupplier($props, $id){
        /* BEGIN DB TRANSACTION */
        DB::beginTransaction();

        try {
            $supplier = $this->supplierModel::find($id);
            if ($supplier) {
                /* UPDATE SUPPLIER */
                $supplier->nama_supplier    = $props['nama_supplier'];
                $supplier->alamat           = $props['alamat'];
                $supplier->no_telp          = $props['no_telp'];
                $supplier->id_user          = $this->returnAuthUser()->id;
                $supplier->update();

                /* COMMIT DB TRANSACTION */
                DB::commit();

                $supplier = SupplierResource::make($supplier);
                return $supplier;
            } else {
                throw new Exception('Catatan tidak ditemukan!');
            }
        } catch (Exception $ex) {
            /* ROLLBACK DB TRANSACTION */
            DB::rollback();

            throw $ex;
        }
    }

    /* DESTROY SUPPLIER */
    public function destroySupplier($id){
        try {
            $supplier = $this->supplierModel::find($id);
            if ($supplier) {
                $supplier->delete();

                return null;
            }

            throw new Exception('Catatan tidak ditemukan!');
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /* DESTROY SELECTED / MULTIPLE SUPPLIER */
    public function destroyMultipleSupplier($props){
        try {
            $supplier = $this->supplierModel::whereIn('id', $props);

            if ($supplier->count() > 0) {
                $supplier->delete();

                return null;
            }

            throw new Exception('Catatan tidak ditemukan!');
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /* FETCH ALL SUPPLIER FOR OPTIONS */
    public function fetchDataOptions($props){
        try {
            /* GET DATA WITH FILTER AS A MODEL */
            $datas = $this->dataFilterPagination($this->supplierModel, $props, null);

            /* RETRIEVE ALL ROW, CONVERT TO ARRAY AND FORMAT AS RESOURCE */
            $supplier = $datas->select('id', 'nama_supplier', 'alamat')->get();

            return $supplier;
        } catch (Exception $ex) {
            throw $ex;
        }
    }
}
