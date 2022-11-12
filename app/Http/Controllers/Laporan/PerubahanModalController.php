<?php
namespace App\Http\Controllers\Laporan;

use Exception;
use Illuminate\Http\Request;
use App\Http\Services\Laporan\PerubahanModalService;
use App\Http\Controllers\BaseController;

class PerubahanModalController extends BaseController
{
    private $perubahanModalServices;
    private $moduleName;

    public function __construct(PerubahanModalService $perubahanModalServices)
    {
        $this->perubahanModalServices = $perubahanModalServices;
        $this->moduleName = 'Perubahan Modal';
    }

    public function dataAkun(Request $request)
    {
        try {
            $props = $this->getBaseQueryParams($request, []);
            $props += [
                'tipe'  => $request['tipe'] ?? null,
            ];
            $akun = $this->perubahanModalServices->fetchAkun($props);

            return $this->returnResponse('success', self::HTTP_OK, 'Daftar transaksi akun', $akun);
        } catch (Exception $ex) {
            return $this->returnExceptionResponse('error', self::HTTP_BAD_REQUEST, $ex);
        }
    }
}
