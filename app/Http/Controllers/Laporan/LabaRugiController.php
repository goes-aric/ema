<?php
namespace App\Http\Controllers\Laporan;

use Exception;
use Illuminate\Http\Request;
use App\Http\Services\Laporan\LabaRugiService;
use App\Http\Controllers\BaseController;

class LabaRugiController extends BaseController
{
    private $labaRugiServices;
    private $moduleName;

    public function __construct(LabaRugiService $labaRugiServices)
    {
        $this->labaRugiServices = $labaRugiServices;
        $this->moduleName = 'Laba Rugi';
    }

    public function dataLabaRugi(Request $request)
    {
        try {
            $props = $this->getBaseQueryParams($request, []);
            $props += [
                'tipe'  => $request['tipe'] ?? null,
            ];
            $akun = $this->labaRugiServices->fetchData($props);

            return $this->returnResponse('success', self::HTTP_OK, 'Data laba rugi', $akun);
        } catch (Exception $ex) {
            return $this->returnExceptionResponse('error', self::HTTP_BAD_REQUEST, $ex);
        }
    }
}
