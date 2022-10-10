<?php
namespace App\Http\Controllers\Akun;

use Exception;
use Illuminate\Http\Request;
use App\Http\Services\Akun\AkunService;
use App\Http\Controllers\BaseController;

class AkunController extends BaseController
{
    private $akunServices;
    private $moduleName;

    public function __construct(AkunService $akunServices)
    {
        $this->akunServices = $akunServices;
        $this->moduleName = 'Akun';
    }

    public function index(Request $request)
    {
        try {
            $props = $this->getBaseQueryParams($request, []);
            $akun = $this->akunServices->fetchLimit($props);

            return $this->returnResponse('success', self::HTTP_OK, 'Daftar akun', $akun);
        } catch (Exception $ex) {
            return $this->returnExceptionResponse('error', self::HTTP_BAD_REQUEST, $ex);
        }
    }

    public function store(Request $request)
    {
        try {
            $rules = [
                'kode_akun'		=> 'required|string|unique:akun',
                'nama_akun'     => 'required|string|max:255',
                'akun_utama'    => 'nullable',
                'tipe_akun'     => 'required',
            ];
            $validator = $this->returnValidator($request->all(), $rules);
            if ($validator->fails()) {
                return $this->returnResponse('error', self::HTTP_UNPROCESSABLE_ENTITY, $validator->errors());
            }

            $akun = $this->akunServices->createAkun($request);
            return $this->returnResponse('success', self::HTTP_CREATED, 'Akun berhasil dibuat', $akun);
        } catch (Exception $ex) {
            return $this->returnExceptionResponse('error', self::HTTP_BAD_REQUEST, $ex);
        }
    }

    public function show($id)
    {
        try {
            $akun = $this->akunServices->fetchById($id);
            return $this->returnResponse('success', self::HTTP_OK, 'Detail akun', $akun);
        } catch (Exception $ex) {
            return $this->returnExceptionResponse('error', self::HTTP_BAD_REQUEST, $ex);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $rules = [
                'kode_akun'		=> 'required|string|unique:akun,kode_akun,'.$id.'',
                'nama_akun'     => 'required|string|max:255',
                'akun_utama'    => 'nullable',
                'tipe_akun'     => 'required',
            ];
            $validator = $this->returnValidator($request->all(), $rules);
            if ($validator->fails()) {
                return $this->returnResponse('error', self::HTTP_UNPROCESSABLE_ENTITY, $validator->errors());
            }

            $akun = $this->akunServices->updateAkun($request, $id);
            return $this->returnResponse('success', self::HTTP_OK, 'Data akun berhasil diperbaharui', $akun);
        } catch (Exception $ex) {
            return $this->returnExceptionResponse('error', self::HTTP_BAD_REQUEST, $ex);
        }
    }

    public function destroy($id)
    {
        try {
            $akun = $this->akunServices->destroyAkun($id);
            return $this->returnResponse('success', self::HTTP_OK, 'Akun berhasil dihapus!', $akun);
        } catch (Exception $ex) {
            return $this->returnExceptionResponse('error', self::HTTP_BAD_REQUEST, $ex);
        }
    }

    public function destroyMultiple(Request $request)
    {
        try {
            $props = $request->data;
            $akun = $this->akunServices->destroyMultipleAkun($props);

            return $this->returnResponse('success', self::HTTP_OK, 'Akun berhasil dihapus!', $akun);
        } catch (Exception $ex) {
            return $this->returnExceptionResponse('error', self::HTTP_BAD_REQUEST, $ex);
        }
    }

    public function fetchDataOptions(Request $request)
    {
        try {
            $props = $this->getBaseQueryParams($request, []);
            $akun = $this->akunServices->fetchDataOptions($props);

            return $this->returnResponse('success', self::HTTP_OK, 'Daftar akun', $akun);
        } catch (Exception $ex) {
            return $this->returnExceptionResponse('error', self::HTTP_BAD_REQUEST, $ex);
        }
    }
}
