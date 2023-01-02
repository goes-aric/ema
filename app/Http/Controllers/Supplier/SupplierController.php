<?php
namespace App\Http\Controllers\Supplier;

use Exception;
use Illuminate\Http\Request;
use App\Http\Services\Supplier\SupplierService;
use App\Http\Controllers\BaseController;

class SupplierController extends BaseController
{
    private $supplierServices;
    private $moduleName;

    public function __construct(SupplierService $supplierServices)
    {
        $this->supplierServices = $supplierServices;
        $this->moduleName = 'Supplier';
    }

    public function index(Request $request)
    {
        try {
            $props = $this->getBaseQueryParams($request, []);
            $supplier = $this->supplierServices->fetchLimit($props);

            return $this->returnResponse('success', self::HTTP_OK, 'Daftar supplier', $supplier);
        } catch (Exception $ex) {
            return $this->returnExceptionResponse('error', self::HTTP_BAD_REQUEST, $ex);
        }
    }

    public function store(Request $request)
    {
        try {
            $rules = [
                'nama_supplier' => 'required|max:255|unique:supplier,nama_supplier,NULL,id,deleted_at,NULL',
                'alamat'        => 'required',
                'no_telp'       => 'nullable',
            ];
            $validator = $this->returnValidator($request->all(), $rules);
            if ($validator->fails()) {
                return $this->returnResponse('error', self::HTTP_UNPROCESSABLE_ENTITY, $validator->errors());
            }

            $supplier = $this->supplierServices->createSupplier($request);
            return $this->returnResponse('success', self::HTTP_CREATED, 'Supplier berhasil dibuat', $supplier);
        } catch (Exception $ex) {
            return $this->returnExceptionResponse('error', self::HTTP_BAD_REQUEST, $ex);
        }
    }

    public function show($id)
    {
        try {
            $supplier = $this->supplierServices->fetchById($id);
            return $this->returnResponse('success', self::HTTP_OK, 'Detail supplier', $supplier);
        } catch (Exception $ex) {
            return $this->returnExceptionResponse('error', self::HTTP_BAD_REQUEST, $ex);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $rules = [
                'nama_supplier' => 'required|max:255|unique:supplier,nama_supplier,'.$id.',id,deleted_at,NULL',
                'alamat'        => 'required',
                'no_telp'       => 'nullable',
            ];
            $validator = $this->returnValidator($request->all(), $rules);
            if ($validator->fails()) {
                return $this->returnResponse('error', self::HTTP_UNPROCESSABLE_ENTITY, $validator->errors());
            }

            $supplier = $this->supplierServices->updateSupplier($request, $id);
            return $this->returnResponse('success', self::HTTP_OK, 'Data supplier berhasil diperbaharui', $supplier);
        } catch (Exception $ex) {
            return $this->returnExceptionResponse('error', self::HTTP_BAD_REQUEST, $ex);
        }
    }

    public function destroy($id)
    {
        try {
            $supplier = $this->supplierServices->destroySupplier($id);
            return $this->returnResponse('success', self::HTTP_OK, 'Supplier berhasil dihapus!', $supplier);
        } catch (Exception $ex) {
            return $this->returnExceptionResponse('error', self::HTTP_BAD_REQUEST, $ex);
        }
    }

    public function destroyMultiple(Request $request)
    {
        try {
            $props = $request->data;
            $supplier = $this->supplierServices->destroyMultipleSupplier($props);

            return $this->returnResponse('success', self::HTTP_OK, 'Supplier berhasil dihapus!', $supplier);
        } catch (Exception $ex) {
            return $this->returnExceptionResponse('error', self::HTTP_BAD_REQUEST, $ex);
        }
    }

    public function fetchDataOptions(Request $request)
    {
        try {
            $props = $this->getBaseQueryParams($request, []);
            $supplier = $this->supplierServices->fetchDataOptions($props);

            return $this->returnResponse('success', self::HTTP_OK, 'Daftar supplier', $supplier);
        } catch (Exception $ex) {
            return $this->returnExceptionResponse('error', self::HTTP_BAD_REQUEST, $ex);
        }
    }
}
