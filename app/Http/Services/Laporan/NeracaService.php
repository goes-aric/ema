<?php
namespace App\Http\Services\Laporan;

use Exception;
use App\Models\ViewJurnalUmum;
use App\Http\Services\BaseService;
use Illuminate\Support\Facades\DB;
use App\Models\Akun;

class NeracaService extends BaseService
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

    /* FETCH DATA AKUN DAN TRANSAKSI */
    public function fetchAkun($props){
        /* GET AKUN UTAMA */
        $akun = $this->akunModel::whereNull('akun_utama')->where('tipe_akun', '=', $props['tipe'])->get();

        foreach ($akun as $item) {
            /* TRANSAKSI SEBELUM PERIODE */
            $transaksiSebelumnya = $this->viewJurnalModel::where('akun_utama', '=', $item->kode_akun);
            if ($item->tipe_akun == 'EKUITAS') {
                $transaksiSebelumnya->orWhere('akun_utama', '=', 'XXX');
            }
            $transaksiSebelumnya->where('tanggal_transaksi', '<', $this->returnDateOnly($props['start']))
                                ->orderBy('kode_akun');

            /* TRANSAKSI PER PERIODE */
            $transaksi = $this->viewJurnalModel::where('akun_utama', '=', $item->kode_akun);
            if ($item->tipe_akun == 'EKUITAS') {
                $transaksi->orWhere('akun_utama', '=', 'XXX');
            }
            $transaksi->where('tanggal_transaksi', '>=', $this->returnDateOnly($props['start']))
                        ->where('tanggal_transaksi', '<=', $this->returnDateOnly($props['end']))
                        ->orderBy('kode_akun');

            $unionData = $transaksi->union($transaksiSebelumnya)
                            ->orderBy('kode_akun')
                            ->get();

            $item->transaksi = $unionData;
        }

        return $akun;
    }
}
