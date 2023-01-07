<?php
namespace App\Http\Services\Laporan;

use Exception;
use App\Models\Pembelian;
use App\Models\PembelianDetail;
use App\Http\Services\BaseService;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\Pembelian\PembelianResource;

class PembelianService extends BaseService
{
    /* PRIVATE VARIABLE */
    private $pembelianModel;
    private $pembelianDetailModel;
    private $carbon;

    public function __construct()
    {
        $this->pembelianModel = new Pembelian();
        $this->pembelianDetailModel = new PembelianDetail();
        $this->carbon = $this->returnCarbon();
    }

    public function fetchData($props) {
        if ($props['type'] == 'rekapitulasi') {
            return $this->fetchRekapitulasi($props);
        } else {
            return $this->fetchItem($props);
        }
    }

    /* FETCH ALL PEMBELIAN */
    public function fetchRekapitulasi($props){
        /* GET DATA WITH FILTER AS A MODEL */
        $datas = $this->dataFilterPagination($this->pembelianModel, $props, null);

        /* RETRIEVE ALL ROW, CONVERT TO ARRAY AND FORMAT AS RESOURCE */
        $datas = $datas->get();
        $pembelian = PembelianResource::collection($datas);

        return $pembelian;
    }

    /* FETCH ALL ITEM PEMBELIAN */
    public function fetchItem($props){
        /* GET DATA WITH FILTER AS A MODEL */
        $datas = $this->pembelianDetailModel::selectRaw("pembelian_detail.id_barang, barang.kode_barang, barang.nama_barang, barang.satuan, pembelian_detail.harga, SUM(pembelian_detail.qty) AS qty, SUM(pembelian_detail.total) AS total")
                ->join('pembelian', 'pembelian_detail.id_pembelian', '=', 'pembelian.id')
                ->join('barang', 'pembelian_detail.id_barang', '=', 'barang.id')
                ->whereDate('pembelian.tanggal', '>=', $this->returnDateOnly($props['start']))
                ->whereDate('pembelian.tanggal', '<=', $this->returnDateOnly($props['end']))
                ->groupByRaw("pembelian_detail.id_barang, barang.kode_barang, barang.nama_barang, barang.satuan, pembelian_detail.harga");

        /* RETRIEVE ALL ROW, CONVERT TO ARRAY AND FORMAT AS RESOURCE */
        $pembelian = $datas->get();

        return $pembelian;
    }
}
