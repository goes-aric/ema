<?php
namespace App\Http\Services\Penjualan;

use Exception;
use App\Models\Penjualan;
use App\Http\Services\BaseService;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\Penjualan\PenjualanResource;

class PenjualanService extends BaseService
{
    /* PRIVATE VARIABLE */
    private $penjualanModel;
    private $carbon;

    public function __construct()
    {
        $this->penjualanModel = new Penjualan();
        $this->carbon = $this->returnCarbon();
    }

    /* FETCH ALL PENJUALAN */
    public function fetchLimit($props){
        /* GET DATA FOR PAGINATION AS A MODEL */
        $getAllData = $this->dataFilterPagination($this->penjualanModel, [], null);
        $totalData = $getAllData->count();

        /* GET DATA WITH FILTER FOR PAGINATION AS A MODEL */
        $getFilterData = $this->dataFilterPagination($this->penjualanModel, $props, null);
        $totalFiltered = $getFilterData->count();

        /* GET DATA WITH FILTER AS A MODEL */
        $datas = $this->dataFilter($this->penjualanModel, $props, null);

        /* RETRIEVE ALL ROW, CONVERT TO ARRAY AND FORMAT AS RESOURCE */
        $datas = $datas->get();
        $datas = PenjualanResource::collection($datas);
        $penjualan = [
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

        return $penjualan;
    }

    /* FETCH PENJUALAN BY ID */
    public function fetchById($id){
        try {
            $penjualan = $this->penjualanModel::find($id);
            if ($penjualan) {
                $penjualan = PenjualanResource::make($penjualan);
                return $penjualan;
            }

            throw new Exception('Catatan tidak ditemukan!');
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /* CREATE NEW PENJUALAN */
    public function createPenjualan($props){
        /* BEGIN DB TRANSACTION */
        DB::beginTransaction();

        try {
            $newID = $this->createNoTransaksi();

            $penjualan = $this->penjualanModel;
            $penjualan->kode_jual               = $newID;
            $penjualan->tanggal                 = $props['tanggal'];
            $penjualan->nominal                 = $props['nominal'];
            $penjualan->uraian                  = $props['uraian'];
            $penjualan->kode_akun_persediaan    = $props['kode_akun_persediaan'];
            $penjualan->kode_akun_penerimaan    = $props['kode_akun_penerimaan'];
            $penjualan->kode_user               = $this->returnAuthUser()->kode_user;
            $penjualan->save();

            /* COMMIT DB TRANSACTION */
            DB::commit();

            $penjualan = PenjualanResource::make($penjualan);
            return $penjualan;
        } catch (Exception $ex) {
            /* ROLLBACK DB TRANSACTION */
            DB::rollback();

            throw $ex;
        }
    }

    /* UPDATE PENJUALAN */
    public function updatePenjualan($props, $id){
        /* BEGIN DB TRANSACTION */
        DB::beginTransaction();

        try {
            $penjualan = $this->penjualanModel::find($id);
            if ($penjualan) {
                /* UPDATE PENJUALAN */
                $penjualan->tanggal                 = $props['tanggal'];
                $penjualan->nominal                 = $props['nominal'];

                $penjualan->uraian                  = $props['uraian'];
                $penjualan->kode_akun_persediaan    = $props['kode_akun_persediaan'];
                $penjualan->kode_akun_penerimaan    = $props['kode_akun_penerimaan'];
                $penjualan->kode_user               = $this->returnAuthUser()->kode_user;
                $penjualan->update();

                /* COMMIT DB TRANSACTION */
                DB::commit();

                $penjualan = PenjualanResource::make($penjualan);
                return $penjualan;
            } else {
                throw new Exception('Catatan tidak ditemukan!');
            }
        } catch (Exception $ex) {
            /* ROLLBACK DB TRANSACTION */
            DB::rollback();

            throw $ex;
        }
    }

    /* DESTROY PENJUALAN */
    public function destroyPenjualan($id){
        try {
            $penjualan = $this->penjualanModel::find($id);
            if ($penjualan) {
                $penjualan->delete();

                return null;
            }

            throw new Exception('Catatan tidak ditemukan!');
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /* DESTROY SELECTED / MULTIPLE PENJUALAN */
    public function destroyMultiplePenjualan($props){
        try {
            $penjualan = $this->penjualanModel::whereIn('id', $props);

            if ($penjualan->count() > 0) {
                $penjualan->delete();

                return null;
            }

            throw new Exception('Catatan tidak ditemukan!');
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /* GENERATE NO TRANSAKSI AUTOMATICALLY */
    public function createNoTransaksi(){
        $year   = $this->carbon::now()->format('Y');

        $newID  = "";
        $maxID  = DB::select('SELECT IFNULL(RIGHT(MAX(kode_jual), 5), 0) AS maxID FROM penjualan WHERE deleted_at IS NULL AND RIGHT(LEFT(kode_jual, 7), 4) = :id', ['id' => $year]);
        $newID  = (int)$maxID[0]->maxID + 1;
        $newID  = 'PJ-'.$year.''.substr("0000000$newID", -5);

        return $newID;
    }
}
