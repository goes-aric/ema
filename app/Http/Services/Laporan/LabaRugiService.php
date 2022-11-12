<?php
namespace App\Http\Services\Laporan;

use Exception;
use App\Models\Akun;
use App\Models\ViewJurnalUmum;
use App\Http\Services\BaseService;
use Illuminate\Support\Facades\DB;

class LabaRugiService extends BaseService
{
    /* PRIVATE VARIABLE */
    private $akunModel;
    private $viewJurnalModel;
    private $carbon;

    public function __construct()
    {
        $this->akunModel = new Akun();
        $this->viewJurnalModel = new ViewJurnalUmum();
        $this->carbon = $this->returnCarbon();
    }

    /* FETCH DATA */
    public function fetchData($props){
        /* GET AKUN UTAMA */
        $akun = $this->akunModel::whereNull('akun_utama')->where('tipe_akun', '=', $props['tipe'])->get();

        foreach ($akun as $item) {
            $transaksi = $this->viewJurnalModel::where('akun_utama', '=', $item->kode_akun)
                            ->where('tanggal_transaksi', '>=', $this->returnDateOnly($props['start']))
                            ->where('tanggal_transaksi', '<=', $this->returnDateOnly($props['end']))
                            ->orderBy('kode_akun')->get();

            $item->transaksi = $transaksi;
        }

        return $akun;
    }
}
