<?php
namespace App\Http\Controllers\Laporan;

use Exception;
use Illuminate\Http\Request;
use App\Http\Services\Laporan\ArusKasService;
use App\Http\Controllers\BaseController;

class ArusKasController extends BaseController
{
    private $arusKasServices;
    private $moduleName;

    public function __construct(ArusKasService $arusKasServices)
    {
        $this->arusKasServices = $arusKasServices;
        $this->moduleName = 'Neraca';
    }

    public function dataAkun(Request $request)
    {
        try {
            $props = $this->getBaseQueryParams($request, []);
            $props += [
                'tipe'  => $request['tipe'] ?? null,
            ];
            $akun = $this->arusKasServices->fetchAkun($props);

            return $this->returnResponse('success', self::HTTP_OK, 'Daftar transaksi akun', $akun);
        } catch (Exception $ex) {
            return $this->returnExceptionResponse('error', self::HTTP_BAD_REQUEST, $ex);
        }
    }
}
