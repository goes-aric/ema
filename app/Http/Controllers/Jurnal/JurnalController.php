<?php
namespace App\Http\Controllers\Jurnal;

use Exception;
use Illuminate\Http\Request;
use App\Http\Services\Jurnal\JurnalService;
use App\Http\Controllers\BaseController;

class JurnalController extends BaseController
{
    private $jurnalServices;
    private $moduleName;

    public function __construct(JurnalService $jurnalServices)
    {
        $this->jurnalServices = $jurnalServices;
        $this->moduleName = 'Jurnal Umum';
    }

    public function list(Request $request)
    {
        try {
            $props = $this->getBaseQueryParams($request, []);
            $jurnal = $this->jurnalServices->fetchAll($props);

            return $this->returnResponse('success', self::HTTP_OK, 'Daftar jurnal umum', $jurnal);
        } catch (Exception $ex) {
            return $this->returnExceptionResponse('error', self::HTTP_BAD_REQUEST, $ex);
        }
    }

    public function index(Request $request)
    {
        try {
            $props = $this->getBaseQueryParams($request, []);
            $jurnal = $this->jurnalServices->fetchLimit($props);

            return $this->returnResponse('success', self::HTTP_OK, 'Daftar jurnal umum', $jurnal);
        } catch (Exception $ex) {
            return $this->returnExceptionResponse('error', self::HTTP_BAD_REQUEST, $ex);
        }
    }

    public function store(Request $request)
    {
        try {
            $rules = [
                'tanggal_transaksi' => 'required|date',
                'deskripsi'         => 'required',
                'sumber'            => 'nullable',
                'gambar'            => 'required',
            ];
            $validator = $this->returnValidator($request->all(), $rules);
            if ($validator->fails()) {
                return $this->returnResponse('error', self::HTTP_UNPROCESSABLE_ENTITY, $validator->errors());
            }

            $jurnal = $this->jurnalServices->createJurnal($request);
            return $this->returnResponse('success', self::HTTP_CREATED, 'Jurnal umum berhasil dibuat', $jurnal);
        } catch (Exception $ex) {
            return $this->returnExceptionResponse('error', self::HTTP_BAD_REQUEST, $ex);
        }
    }

    public function show($id)
    {
        try {
            $jurnal = $this->jurnalServices->fetchById($id);
            return $this->returnResponse('success', self::HTTP_OK, 'Detail jurnal umum', $jurnal);
        } catch (Exception $ex) {
            return $this->returnExceptionResponse('error', self::HTTP_BAD_REQUEST, $ex);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $rules = [
                'tanggal_transaksi' => 'required|date',
                'deskripsi'         => 'required',
                'sumber'            => 'nullable',
                'gambar'            => 'nullable',
            ];
            $validator = $this->returnValidator($request->all(), $rules);
            if ($validator->fails()) {
                return $this->returnResponse('error', self::HTTP_UNPROCESSABLE_ENTITY, $validator->errors());
            }

            $jurnal = $this->jurnalServices->updateJurnal($request, $id);
            return $this->returnResponse('success', self::HTTP_OK, 'Data jurnal umum berhasil diperbaharui', $jurnal);
        } catch (Exception $ex) {
            return $this->returnExceptionResponse('error', self::HTTP_BAD_REQUEST, $ex);
        }
    }

    public function destroy($id)
    {
        try {
            $jurnal = $this->jurnalServices->destroyJurnal($id);
            return $this->returnResponse('success', self::HTTP_OK, 'Jurnal umum berhasil dihapus!', $jurnal);
        } catch (Exception $ex) {
            return $this->returnExceptionResponse('error', self::HTTP_BAD_REQUEST, $ex);
        }
    }

    public function destroyMultiple(Request $request)
    {
        try {
            $props = $request->data;
            $jurnal = $this->jurnalServices->destroyMultipleJurnal($props);

            return $this->returnResponse('success', self::HTTP_OK, 'Jurnal umum berhasil dihapus!', $jurnal);
        } catch (Exception $ex) {
            return $this->returnExceptionResponse('error', self::HTTP_BAD_REQUEST, $ex);
        }
    }
}
