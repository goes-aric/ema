<?php
namespace App\Http\Services\Laporan;

use Exception;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Http\Services\BaseService;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\Penjualan\PenjualanResource;

class PenjualanService extends BaseService
{
    /* PRIVATE VARIABLE */
    private $penjualanModel;
    private $penjualanDetailModel;
    private $carbon;

    public function __construct()
    {
        $this->penjualanModel = new Penjualan();
        $this->penjualanDetailModel = new PenjualanDetail();
        $this->carbon = $this->returnCarbon();
    }

    public function fetchData($props) {
        if ($props['type'] == 'rekapitulasi') {
            return $this->fetchRekapitulasi($props);
        } elseif ($props['type'] == 'item') {
            return $this->fetchItem($props);
        } else {
            return $this->fetchCharts($props);
        }
    }

    /* FETCH ALL PENJUALAN */
    public function fetchRekapitulasi($props){
        /* GET DATA WITH FILTER AS A MODEL */
        $datas = $this->dataFilterPagination($this->penjualanModel, $props, null);

        /* RETRIEVE ALL ROW, CONVERT TO ARRAY AND FORMAT AS RESOURCE */
        $datas = $datas->get();
        $penjualan = PenjualanResource::collection($datas);

        return $penjualan;
    }

    /* FETCH ALL ITEM PENJUALAN */
    public function fetchItem($props){
        /* GET DATA WITH FILTER AS A MODEL */
        $datas = $this->penjualanDetailModel::selectRaw("penjualan_detail.id_barang, barang.kode_barang, barang.nama_barang, barang.satuan, penjualan_detail.harga, SUM(penjualan_detail.qty) AS qty, SUM(penjualan_detail.total) AS total")
                ->join('penjualan', 'penjualan_detail.id_penjualan', '=', 'penjualan.id')
                ->join('barang', 'penjualan_detail.id_barang', '=', 'barang.id')
                ->whereDate('penjualan.tanggal', '>=', $this->returnDateOnly($props['start']))
                ->whereDate('penjualan.tanggal', '<=', $this->returnDateOnly($props['end']))
                ->groupByRaw("penjualan_detail.id_barang, barang.kode_barang, barang.nama_barang, barang.satuan, penjualan_detail.harga");

        /* RETRIEVE ALL ROW, CONVERT TO ARRAY AND FORMAT AS RESOURCE */
        $penjualan = $datas->get();

        return $penjualan;
    }

    /* FETCH ALL ITEM PENJUALAN */
    public function fetchCharts($props){
        /* GET DATA WITH FILTER AS A MODEL */
        $datas = $this->penjualanDetailModel::selectRaw("penjualan_detail.id_barang, barang.kode_barang, barang.nama_barang, barang.satuan, SUM(penjualan_detail.qty) AS qty")
                ->join('penjualan', 'penjualan_detail.id_penjualan', '=', 'penjualan.id')
                ->join('barang', 'penjualan_detail.id_barang', '=', 'barang.id')
                ->whereDate('penjualan.tanggal', '>=', $this->returnDateOnly($props['start']))
                ->whereDate('penjualan.tanggal', '<=', $this->returnDateOnly($props['end']))
                ->groupByRaw("penjualan_detail.id_barang, barang.kode_barang, barang.nama_barang, barang.satuan")
                ->orderBy("qty", "DESC")
                ->limit(10);

        /* RETRIEVE ALL ROW, CONVERT TO ARRAY AND FORMAT AS RESOURCE */
        $penjualan = $datas->get();

        return $penjualan;
    }
}
