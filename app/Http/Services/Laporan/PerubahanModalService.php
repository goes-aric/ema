<?php
namespace App\Http\Services\Laporan;

use Exception;
use App\Models\Akun;
use App\Models\ViewJurnalUmum;
use App\Http\Services\BaseService;
use Illuminate\Support\Facades\DB;

class PerubahanModalService extends BaseService
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
        /* TRANSAKSI SEBELUM PERIODE */
        $transaksiSebelumnya = $this->viewJurnalModel::where('tipe_akun', '=', $props['tipe'])
                                ->where('tanggal_transaksi', '<', $this->returnDateOnly($props['start']))
                                ->orderBy('kode_akun');

        /* TRANSAKSI PER PERIODE */
        $transaksi = $this->viewJurnalModel::where('tipe_akun', '=', $props['tipe'])
                        ->where('tanggal_transaksi', '>=', $this->returnDateOnly($props['start']))
                        ->where('tanggal_transaksi', '<=', $this->returnDateOnly($props['end']))
                        ->orderBy('kode_akun');

        $unionData = $transaksi->union($transaksiSebelumnya)
                        ->orderBy('kode_akun')
                        ->get();

        return $unionData;
    }
}
