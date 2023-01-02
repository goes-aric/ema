<?php
namespace App\Http\Services\Barang;

use Exception;
use App\Models\Barang;
use App\Http\Services\BaseService;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\Barang\BarangResource;

class BarangService extends BaseService
{
    /* PRIVATE VARIABLE */
    private $barangModel;

    public function __construct()
    {
        $this->barangModel = new Barang();
    }

    /* FETCH ALL BARANG */
    public function fetchLimit($props){
        /* GET DATA FOR PAGINATION AS A MODEL */
        $getAllData = $this->dataFilterPagination($this->barangModel, [], null);
        $totalData = $getAllData->count();

        /* GET DATA WITH FILTER FOR PAGINATION AS A MODEL */
        $getFilterData = $this->dataFilterPagination($this->barangModel, $props, null);
        $totalFiltered = $getFilterData->count();

        /* GET DATA WITH FILTER AS A MODEL */
        $datas = $this->dataFilter($this->barangModel, $props, null);

        /* RETRIEVE ALL ROW, CONVERT TO ARRAY AND FORMAT AS RESOURCE */
        $datas = $datas->get();
        $datas = BarangResource::collection($datas);
        $barang = [
            "total" => $totalData,
            "total_filter" => $totalFiltered,
            "per_page" => $props['take'],
            "current_page" => $props['skip'] == 0 ? 1 : ($props['skip'] + 1),
            "last_page" => ceil($totalFiltered / $props['take']),
            "from" => $totalFiltered === 0 ? 0 : ($props['skip'] != 0 ? ($props['skip'] * $props['take']) + 1 : 1),
            "to" => $totalFiltered === 0 ? 0 : ($props['skip'] * $props['take']) + $datas->count(),
            "show" => [
                ["number" => 25, "name" => "25"], ["number" => 50, "name" => "50"], ["number" => 100, "name" => "100"]
            ],
            "data" => $datas
        ];

        return $barang;
    }

    /* FETCH BARANG BY ID */
    public function fetchById($id){
        try {
            $barang = $this->barangModel::find($id);
            if ($barang) {
                $barang = BarangResource::make($barang);
                return $barang;
            }

            throw new Exception('Catatan tidak ditemukan!');
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /* CREATE NEW BARANG */
    public function createBarang($props){
        /* BEGIN DB TRANSACTION */
        DB::beginTransaction();

        try {
            $barang = $this->barangModel;
            $barang->kode_barang    = $props['kode_barang'];
            $barang->nama_barang    = $props['nama_barang'];
            $barang->satuan         = $props['satuan'];
            $barang->harga_jual     = $props['harga_jual'];
            $barang->status         = $props['status'];
            $barang->id_user        = $this->returnAuthUser()->id;
            $barang->save();

            /* COMMIT DB TRANSACTION */
            DB::commit();

            $barang = BarangResource::make($barang);
            return $barang;
        } catch (Exception $ex) {
            /* ROLLBACK DB TRANSACTION */
            DB::rollback();

            throw $ex;
        }
    }

    /* UPDATE BARANG */
    public function updateBarang($props, $id){
        /* BEGIN DB TRANSACTION */
        DB::beginTransaction();

        try {
            $barang = $this->barangModel::find($id);
            if ($barang) {
                /* UPDATE BARANG */
                $barang->kode_barang    = $props['kode_barang'];
                $barang->nama_barang    = $props['nama_barang'];
                $barang->satuan         = $props['satuan'];
                $barang->harga_jual     = $props['harga_jual'];
                $barang->status         = $props['status'];
                $barang->id_user        = $this->returnAuthUser()->id;
                $barang->update();

                /* COMMIT DB TRANSACTION */
                DB::commit();

                $barang = BarangResource::make($barang);
                return $barang;
            } else {
                throw new Exception('Catatan tidak ditemukan!');
            }
        } catch (Exception $ex) {
            /* ROLLBACK DB TRANSACTION */
            DB::rollback();

            throw $ex;
        }
    }

    /* DESTROY BARANG */
    public function destroyBarang($id){
        try {
            $barang = $this->barangModel::find($id);
            if ($barang) {
                if ($barang->status_digunakan == 1) {
                    throw new Exception('Barang sudah digunakan dalam transaksi, hapus data barang tidak bisa dilanjutkan!');
                }

                $barang->delete();
                return null;
            }

            throw new Exception('Catatan tidak ditemukan!');
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /* DESTROY SELECTED / MULTIPLE BARANG */
    public function destroyMultipleBarang($props){
        try {
            $barang = $this->barangModel::where('status_digunakan', '=', 0)->whereIn('id', $props);

            if ($barang->count() > 0) {
                $barang->delete();

                return null;
            }

            throw new Exception('Catatan tidak ditemukan!');
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /* FETCH ALL BARANG FOR OPTIONS */
    public function fetchDataOptions($props){
        try {
            /* GET DATA WITH FILTER AS A MODEL */
            $datas = $this->dataFilterPagination($this->barangModel, $props, null);

            /* RETRIEVE ALL ROW, CONVERT TO ARRAY AND FORMAT AS RESOURCE */
            $barang = $datas->select('id', 'kode_barang', 'nama_barang', 'satuan', 'harga_beli', 'harga_jual')->where('status', '=', 1)->get();

            return $barang;
        } catch (Exception $ex) {
            throw $ex;
        }
    }
}
