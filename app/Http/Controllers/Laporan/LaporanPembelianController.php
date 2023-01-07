<?php
namespace App\Http\Controllers\Laporan;

use Exception;
use Illuminate\Http\Request;
use App\Http\Services\Laporan\PembelianService;
use App\Http\Controllers\BaseController;

class LaporanPembelianController extends BaseController
{
    private $pembelianServices;
    private $moduleName;

    public function __construct(PembelianService $pembelianServices)
    {
        $this->pembelianServices = $pembelianServices;
        $this->moduleName = 'Laporan Pembelian';
    }

    public function dataPembelian(Request $request)
    {
        try {
            $props = $this->getBaseQueryParams($request, []);
            $props += [
                'type'  => $request['type'] ?? null,
            ];
            $pembelian = $this->pembelianServices->fetchData($props);

            return $this->returnResponse('success', self::HTTP_OK, 'Laporan pembelian', $pembelian);
        } catch (Exception $ex) {
            return $this->returnExceptionResponse('error', self::HTTP_BAD_REQUEST, $ex);
        }
    }
}
