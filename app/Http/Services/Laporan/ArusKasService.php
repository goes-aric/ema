<?php
namespace App\Http\Services\Laporan;

use Exception;
use App\Models\Akun;
use App\Models\ViewJurnalUmum;
use App\Http\Services\BaseService;
use Illuminate\Support\Facades\DB;

class ArusKasService extends BaseService
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
        /* TIPE ARUS KAS */
        $tipeArusKas = [
            [
                'id'        => 'operasional',
                'name'      => 'Arus Kas dari Aktivitas Operasional',
                'transaksi' => [],
                'saldo_awal'=> []
            ],
            [
                'id'      => 'investasi',
                'name'    => 'Arus Kas dari Aktivitas Inventasi',
                'transaksi' => [],
                'saldo_awal'=> []
            ],
            [
                'id'      => 'pendanaan',
                'name'    => 'Arus Kas dari Aktivitas Keuangan',
                'transaksi' => [],
                'saldo_awal'=> []
            ],
        ];

        foreach ($tipeArusKas as $index => $item) {
            $akun = $this->akunModel::selectRaw('kode_akun, nama_akun, akun_utama, tipe_akun, arus_kas_tipe, 0 AS saldo')
                        ->where('arus_kas_tipe', '=', $item['id'])
                        ->orderBy('kode_akun');

            /* TRANSAKSI PER PERIODE */
            $transaksi = $this->viewJurnalModel::selectRaw("kode_akun, nama_akun, akun_utama, tipe_akun, arus_kas_tipe, IF((tipe_akun = 'AKTIVA' or tipe_akun = 'BEBAN'), -(debet-kredit), (kredit-debet)) AS saldo")
                            ->where('arus_kas_tipe', '=', $item['id'])
                            ->where('tanggal_transaksi', '>=', $this->returnDateOnly($props['start']))
                            ->where('tanggal_transaksi', '<=', $this->returnDateOnly($props['end']))
                            ->orderBy('kode_akun');

            $unionData = $transaksi->union($akun)
                            ->orderBy('kode_akun')
                            ->get();

            $tipeArusKas[$index]['transaksi'] = $unionData;

            /* SALDO AWAL */
            $saldoAwal = $this->viewJurnalModel::selectRaw("kode_akun, nama_akun, akun_utama, tipe_akun, arus_kas_tipe, IF((tipe_akun = 'AKTIVA' or tipe_akun = 'BEBAN'), -(debet-kredit), (kredit-debet)) AS saldo")
                            ->where('arus_kas_tipe', '=', $item['id'])
                            ->where('tanggal_transaksi', '<', $this->returnDateOnly($props['start']))
                            ->orderBy('kode_akun')
                            ->get();

            $tipeArusKas[$index]['saldo_awal'] = $saldoAwal;
        }

        return $tipeArusKas;
    }
}
