<?php
namespace App\Http\Controllers\Barang;

use Exception;
use Illuminate\Http\Request;
use App\Http\Services\Barang\BarangService;
use App\Http\Controllers\BaseController;

class BarangController extends BaseController
{
    private $barangServices;
    private $moduleName;

    public function __construct(BarangService $barangServices)
    {
        $this->barangServices = $barangServices;
        $this->moduleName = 'Barang';
    }

    public function index(Request $request)
    {
        try {
            $props = $this->getBaseQueryParams($request, []);
            $barang = $this->barangServices->fetchLimit($props);

            return $this->returnResponse('success', self::HTTP_OK, 'Daftar barang', $barang);
        } catch (Exception $ex) {
            return $this->returnExceptionResponse('error', self::HTTP_BAD_REQUEST, $ex);
        }
    }

    public function store(Request $request)
    {
        try {
            $rules = [
                'kode_barang'   => 'required|max:255|unique:barang,kode_barang,NULL,id,deleted_at,NULL',
                'nama_barang'   => 'required',
                'satuan'        => 'required',
                'harga_jual'    => 'required|numeric',
                'status'        => 'required',
            ];
            $validator = $this->returnValidator($request->all(), $rules);
            if ($validator->fails()) {
                return $this->returnResponse('error', self::HTTP_UNPROCESSABLE_ENTITY, $validator->errors());
            }

            $barang = $this->barangServices->createBarang($request);
            return $this->returnResponse('success', self::HTTP_CREATED, 'Barang berhasil dibuat', $barang);
        } catch (Exception $ex) {
            return $this->returnExceptionResponse('error', self::HTTP_BAD_REQUEST, $ex);
        }
    }

    public function show($id)
    {
        try {
            $barang = $this->barangServices->fetchById($id);
            return $this->returnResponse('success', self::HTTP_OK, 'Detail barang', $barang);
        } catch (Exception $ex) {
            return $this->returnExceptionResponse('error', self::HTTP_BAD_REQUEST, $ex);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $rules = [
                'kode_barang'   => 'required|max:255|unique:barang,kode_barang,'.$id.',id,deleted_at,NULL',
                'nama_barang'   => 'required',
                'satuan'        => 'required',
                'harga_jual'    => 'required|numeric',
                'status'        => 'required',
            ];
            $validator = $this->returnValidator($request->all(), $rules);
            if ($validator->fails()) {
                return $this->returnResponse('error', self::HTTP_UNPROCESSABLE_ENTITY, $validator->errors());
            }

            $barang = $this->barangServices->updateBarang($request, $id);
            return $this->returnResponse('success', self::HTTP_OK, 'Data barang berhasil diperbaharui', $barang);
        } catch (Exception $ex) {
            return $this->returnExceptionResponse('error', self::HTTP_BAD_REQUEST, $ex);
        }
    }

    public function destroy($id)
    {
        try {
            $barang = $this->barangServices->destroyBarang($id);
            return $this->returnResponse('success', self::HTTP_OK, 'Barang berhasil dihapus!', $barang);
        } catch (Exception $ex) {
            return $this->returnExceptionResponse('error', self::HTTP_BAD_REQUEST, $ex);
        }
    }

    public function destroyMultiple(Request $request)
    {
        try {
            $props = $request->data;
            $barang = $this->barangServices->destroyMultipleBarang($props);

            return $this->returnResponse('success', self::HTTP_OK, 'Barang berhasil dihapus!', $barang);
        } catch (Exception $ex) {
            return $this->returnExceptionResponse('error', self::HTTP_BAD_REQUEST, $ex);
        }
    }

    public function fetchDataOptions(Request $request)
    {
        try {
            $props = $this->getBaseQueryParams($request, []);
            $barang = $this->barangServices->fetchDataOptions($props);

            return $this->returnResponse('success', self::HTTP_OK, 'Daftar barang', $barang);
        } catch (Exception $ex) {
            return $this->returnExceptionResponse('error', self::HTTP_BAD_REQUEST, $ex);
        }
    }
}
