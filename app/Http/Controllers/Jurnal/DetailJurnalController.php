<?php
namespace App\Http\Controllers\Jurnal;

use Exception;
use Illuminate\Http\Request;
use App\Http\Services\Jurnal\DetailJurnalService;
use App\Http\Controllers\BaseController;

class DetailJurnalController extends BaseController
{
    private $detailServices;
    private $moduleName;

    public function __construct(DetailJurnalService $detailServices)
    {
        $this->detailServices = $detailServices;
        $this->moduleName = 'Detail jurnal Umum';
    }

    public function index(Request $request)
    {
        try {
            $props = $this->getBaseQueryParams($request, []);
            $props += [
                'no_jurnal'  => $request['no_jurnal'] ?? null
            ];
            $detail = $this->detailServices->fetchLimit($props);

            return $this->returnResponse('success', self::HTTP_OK, 'Daftar detail jurnal umum', $detail);
        } catch (Exception $ex) {
            return $this->returnExceptionResponse('error', self::HTTP_BAD_REQUEST, $ex);
        }
    }

    public function store(Request $request)
    {
        try {
            $rules = [
                'no_jurnal' => 'required',
                'kode_akun' => 'required',
                'nama_akun' => 'required',
                'debet'     => 'required|numeric',
                'kredit'    => 'required|numeric',
            ];
            $validator = $this->returnValidator($request->all(), $rules);
            if ($validator->fails()) {
                return $this->returnResponse('error', self::HTTP_UNPROCESSABLE_ENTITY, $validator->errors());
            }

            $detail = $this->detailServices->createDetail($request);
            return $this->returnResponse('success', self::HTTP_CREATED, 'Detail jurnal umum berhasil dibuat', $detail);
        } catch (Exception $ex) {
            return $this->returnExceptionResponse('error', self::HTTP_BAD_REQUEST, $ex);
        }
    }

    public function show($id)
    {
        try {
            $detail = $this->detailServices->fetchById($id);
            return $this->returnResponse('success', self::HTTP_OK, 'Detail jurnal umum', $detail);
        } catch (Exception $ex) {
            return $this->returnExceptionResponse('error', self::HTTP_BAD_REQUEST, $ex);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $rules = [
                'no_jurnal' => 'required',
                'kode_akun' => 'required',
                'nama_akun' => 'required',
                'debet'     => 'required|numeric',
                'kredit'    => 'required|numeric',
            ];
            $validator = $this->returnValidator($request->all(), $rules);
            if ($validator->fails()) {
                return $this->returnResponse('error', self::HTTP_UNPROCESSABLE_ENTITY, $validator->errors());
            }

            $detail = $this->detailServices->updateDetail($request, $id);
            return $this->returnResponse('success', self::HTTP_OK, 'Data detail jurnal umum berhasil diperbaharui', $detail);
        } catch (Exception $ex) {
            return $this->returnExceptionResponse('error', self::HTTP_BAD_REQUEST, $ex);
        }
    }

    public function destroy($id)
    {
        try {
            $detail = $this->detailServices->destroyDetail($id);
            return $this->returnResponse('success', self::HTTP_OK, 'Detail jurnal umum berhasil dihapus!', $detail);
        } catch (Exception $ex) {
            return $this->returnExceptionResponse('error', self::HTTP_BAD_REQUEST, $ex);
        }
    }

    public function destroyMultiple(Request $request)
    {
        try {
            $props = $request->data;
            $detail = $this->detailServices->destroyMultipleDetail($props);

            return $this->returnResponse('success', self::HTTP_OK, 'Detail jurnal umum berhasil dihapus!', $detail);
        } catch (Exception $ex) {
            return $this->returnExceptionResponse('error', self::HTTP_BAD_REQUEST, $ex);
        }
    }
}
