<?php
namespace App\Http\Controllers\Pembelian;

use Exception;
use Illuminate\Http\Request;
use App\Http\Services\Pembelian\PembelianService;
use App\Http\Controllers\BaseController;

class PembelianController extends BaseController
{
    private $pembelianServices;
    private $moduleName;

    public function __construct(PembelianService $pembelianServices)
    {
        $this->pembelianServices = $pembelianServices;
        $this->moduleName = 'Pembelian';
    }

    public function list(Request $request)
    {
        try {
            $props = $this->getBaseQueryParams($request, []);
            $pembelian = $this->pembelianServices->fetchAll($props);

            return $this->returnResponse('success', self::HTTP_OK, 'Daftar pembelian', $pembelian);
        } catch (Exception $ex) {
            return $this->returnExceptionResponse('error', self::HTTP_BAD_REQUEST, $ex);
        }
    }

    public function index(Request $request)
    {
        try {
            $props = $this->getBaseQueryParams($request, []);
            $pembelian = $this->pembelianServices->fetchLimit($props);

            return $this->returnResponse('success', self::HTTP_OK, 'Daftar pembelian', $pembelian);
        } catch (Exception $ex) {
            return $this->returnExceptionResponse('error', self::HTTP_BAD_REQUEST, $ex);
        }
    }

    public function store(Request $request)
    {
        try {
            $rules = [
                'tanggal'		        => 'required|date',
                'nominal'               => 'required|numeric',
                'metode_bayar'          => 'required',
                'uraian'                => 'nullable',
                'kode_akun_persediaan'  => 'required',
                'kode_akun_pembayaran'  => 'required',
                'gambar'                => 'required',
            ];
            $validator = $this->returnValidator($request->all(), $rules);
            if ($validator->fails()) {
                return $this->returnResponse('error', self::HTTP_UNPROCESSABLE_ENTITY, $validator->errors());
            }

            $pembelian = $this->pembelianServices->createPembelian($request);
            return $this->returnResponse('success', self::HTTP_CREATED, 'Pembelian berhasil dibuat', $pembelian);
        } catch (Exception $ex) {
            return $this->returnExceptionResponse('error', self::HTTP_BAD_REQUEST, $ex);
        }
    }

    public function show($id)
    {
        try {
            $pembelian = $this->pembelianServices->fetchById($id);
            return $this->returnResponse('success', self::HTTP_OK, 'Detail pembelian', $pembelian);
        } catch (Exception $ex) {
            return $this->returnExceptionResponse('error', self::HTTP_BAD_REQUEST, $ex);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $rules = [
                'tanggal'		        => 'required|date',
                'nominal'               => 'required|numeric',
                'metode_bayar'          => 'required',
                'uraian'                => 'nullable',
                'kode_akun_persediaan'  => 'required',
                'kode_akun_pembayaran'  => 'required',
                'gambar'                => 'nullable',
            ];
            $validator = $this->returnValidator($request->all(), $rules);
            if ($validator->fails()) {
                return $this->returnResponse('error', self::HTTP_UNPROCESSABLE_ENTITY, $validator->errors());
            }

            $pembelian = $this->pembelianServices->updatePembelian($request, $id);
            return $this->returnResponse('success', self::HTTP_OK, 'Data pembelian berhasil diperbaharui', $pembelian);
        } catch (Exception $ex) {
            return $this->returnExceptionResponse('error', self::HTTP_BAD_REQUEST, $ex);
        }
    }

    public function destroy($id)
    {
        try {
            $pembelian = $this->pembelianServices->destroyPembelian($id);
            return $this->returnResponse('success', self::HTTP_OK, 'Pembelian berhasil dihapus!', $pembelian);
        } catch (Exception $ex) {
            return $this->returnExceptionResponse('error', self::HTTP_BAD_REQUEST, $ex);
        }
    }

    public function destroyMultiple(Request $request)
    {
        try {
            $props = $request->data;
            $pembelian = $this->pembelianServices->destroyMultiplePembelian($props);

            return $this->returnResponse('success', self::HTTP_OK, 'Pembelian berhasil dihapus!', $pembelian);
        } catch (Exception $ex) {
            return $this->returnExceptionResponse('error', self::HTTP_BAD_REQUEST, $ex);
        }
    }

    public function charts(Request $request)
    {
        try {
            $pembelian = $this->pembelianServices->charts();

            return $this->returnResponse('success', self::HTTP_OK, 'Grafik pembelian', $pembelian);
        } catch (Exception $ex) {
            return $this->returnExceptionResponse('error', self::HTTP_BAD_REQUEST, $ex);
        }
    }
}
