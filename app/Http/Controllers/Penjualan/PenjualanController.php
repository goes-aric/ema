<?php
namespace App\Http\Controllers\Penjualan;

use Exception;
use Illuminate\Http\Request;
use App\Http\Services\Penjualan\PenjualanService;
use App\Http\Controllers\BaseController;

class PenjualanController extends BaseController
{
    private $penjualanServices;
    private $moduleName;

    public function __construct(PenjualanService $penjualanServices)
    {
        $this->penjualanServices = $penjualanServices;
        $this->moduleName = 'Penjualan';
    }

    public function list(Request $request)
    {
        try {
            $props = $this->getBaseQueryParams($request, []);
            $penjualan = $this->penjualanServices->fetchAll($props);

            return $this->returnResponse('success', self::HTTP_OK, 'Daftar penjualan', $penjualan);
        } catch (Exception $ex) {
            return $this->returnExceptionResponse('error', self::HTTP_BAD_REQUEST, $ex);
        }
    }

    public function index(Request $request)
    {
        try {
            $props = $this->getBaseQueryParams($request, []);
            $penjualan = $this->penjualanServices->fetchLimit($props);

            return $this->returnResponse('success', self::HTTP_OK, 'Daftar penjualan', $penjualan);
        } catch (Exception $ex) {
            return $this->returnExceptionResponse('error', self::HTTP_BAD_REQUEST, $ex);
        }
    }

    public function store(Request $request)
    {
        try {
            $rules = [
                'no_transaksi'  => 'required',
                'tanggal'       => 'required|date',
                'total'         => 'required|numeric',
                'diskon'        => 'required|numeric',
                'grand_total'   => 'required|numeric',
                'gambar'        => 'nullable',
                'catatan'       => 'nullable',
            ];
            $validator = $this->returnValidator($request->all(), $rules);
            if ($validator->fails()) {
                return $this->returnResponse('error', self::HTTP_UNPROCESSABLE_ENTITY, $validator->errors());
            }

            $penjualan = $this->penjualanServices->createPenjualan($request);
            return $this->returnResponse('success', self::HTTP_CREATED, 'Penjualan berhasil dibuat', $penjualan);
        } catch (Exception $ex) {
            return $this->returnExceptionResponse('error', self::HTTP_BAD_REQUEST, $ex);
        }
    }

    public function show($id)
    {
        try {
            $penjualan = $this->penjualanServices->fetchById($id);
            return $this->returnResponse('success', self::HTTP_OK, 'Detail penjualan', $penjualan);
        } catch (Exception $ex) {
            return $this->returnExceptionResponse('error', self::HTTP_BAD_REQUEST, $ex);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $rules = [
                'tanggal'       => 'required|date',
                'total'         => 'required|numeric',
                'diskon'        => 'required|numeric',
                'grand_total'   => 'required|numeric',
                'gambar'        => 'nullable',
                'catatan'       => 'nullable',
            ];
            $validator = $this->returnValidator($request->all(), $rules);
            if ($validator->fails()) {
                return $this->returnResponse('error', self::HTTP_UNPROCESSABLE_ENTITY, $validator->errors());
            }

            $penjualan = $this->penjualanServices->updatePenjualan($request, $id);
            return $this->returnResponse('success', self::HTTP_OK, 'Data penjualan berhasil diperbaharui', $penjualan);
        } catch (Exception $ex) {
            return $this->returnExceptionResponse('error', self::HTTP_BAD_REQUEST, $ex);
        }
    }

    public function destroy($id)
    {
        try {
            $penjualan = $this->penjualanServices->destroyPenjualan($id);
            return $this->returnResponse('success', self::HTTP_OK, 'Penjualan berhasil dihapus!', $penjualan);
        } catch (Exception $ex) {
            return $this->returnExceptionResponse('error', self::HTTP_BAD_REQUEST, $ex);
        }
    }

    public function destroyMultiple(Request $request)
    {
        try {
            $props = $request->data;
            $penjualan = $this->penjualanServices->destroyMultiplePenjualan($props);

            return $this->returnResponse('success', self::HTTP_OK, 'Penjualan berhasil dihapus!', $penjualan);
        } catch (Exception $ex) {
            return $this->returnExceptionResponse('error', self::HTTP_BAD_REQUEST, $ex);
        }
    }

    public function getInvoiceNumber()
    {
        try {
            $penjualan = $this->penjualanServices->createNoTransaksi();
            return $this->returnResponse('success', self::HTTP_OK, 'No Invoice', $penjualan);
        } catch (Exception $ex) {
            return $this->returnExceptionResponse('error', self::HTTP_BAD_REQUEST, $ex);
        }
    }

    public function charts(Request $request)
    {
        try {
            $penjualan = $this->penjualanServices->charts();

            return $this->returnResponse('success', self::HTTP_OK, 'Grafik penjualan', $penjualan);
        } catch (Exception $ex) {
            return $this->returnExceptionResponse('error', self::HTTP_BAD_REQUEST, $ex);
        }
    }
}
