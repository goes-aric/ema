<?php
namespace App\Http\Controllers\Laporan;

use Exception;
use Illuminate\Http\Request;
use App\Http\Services\Laporan\PenjualanService;
use App\Http\Controllers\BaseController;

class LaporanPenjualanController extends BaseController
{
    private $penjualanServices;
    private $moduleName;

    public function __construct(PenjualanService $penjualanServices)
    {
        $this->penjualanServices = $penjualanServices;
        $this->moduleName = 'Laporan Penjualan';
    }

    public function dataPenjualan(Request $request)
    {
        try {
            $props = $this->getBaseQueryParams($request, []);
            $props += [
                'type'  => $request['type'] ?? null,
            ];
            $penjualan = $this->penjualanServices->fetchData($props);

            return $this->returnResponse('success', self::HTTP_OK, 'Laporan penjualan', $penjualan);
        } catch (Exception $ex) {
            return $this->returnExceptionResponse('error', self::HTTP_BAD_REQUEST, $ex);
        }
    }
}
