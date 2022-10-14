<?php
namespace App\Http\Services\Pembelian;

use Exception;
use App\Models\Pembelian;
use App\Http\Services\BaseService;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\Pembelian\PembelianResource;

class PembelianService extends BaseService
{
    /* PRIVATE VARIABLE */
    private $pembelianModel;
    private $carbon;

    public function __construct()
    {
        $this->pembelianModel = new Pembelian();
        $this->carbon = $this->returnCarbon();
    }

    /* FETCH ALL PEMBELIAN */
    public function fetchLimit($props){
        /* GET DATA FOR PAGINATION AS A MODEL */
        $getAllData = $this->dataFilterPagination($this->pembelianModel, [], null);
        $totalData = $getAllData->count();

        /* GET DATA WITH FILTER FOR PAGINATION AS A MODEL */
        $getFilterData = $this->dataFilterPagination($this->pembelianModel, $props, null);
        $totalFiltered = $getFilterData->count();

        /* GET DATA WITH FILTER AS A MODEL */
        $datas = $this->dataFilter($this->pembelianModel, $props, null);

        /* RETRIEVE ALL ROW, CONVERT TO ARRAY AND FORMAT AS RESOURCE */
        $datas = $datas->get();
        $datas = PembelianResource::collection($datas);
        $pembelian = [
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

        return $pembelian;
    }

    /* FETCH PEMBELIAN BY ID */
    public function fetchById($id){
        try {
            $pembelian = $this->pembelianModel::find($id);
            if ($pembelian) {
                $pembelian = PembelianResource::make($pembelian);
                return $pembelian;
            }

            throw new Exception('Catatan tidak ditemukan!');
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /* CREATE NEW PEMBELIAN */
    public function createPembelian($props){
        /* BEGIN DB TRANSACTION */
        DB::beginTransaction();

        try {
            $newID = $this->createNoTransaksi();

            $pembelian = $this->pembelianModel;
            $pembelian->kode_beli               = $newID;
            $pembelian->tanggal                 = $props['tanggal'];
            $pembelian->nominal                 = $props['nominal'];
            $pembelian->metode_bayar            = $props['metode_bayar'];
            $pembelian->uraian                  = $props['uraian'];
            $pembelian->kode_akun_persediaan    = $props['kode_akun_persediaan'];
            $pembelian->kode_akun_pembayaran    = $props['kode_akun_pembayaran'];
            $pembelian->kode_user               = $this->returnAuthUser()->kode_user;
            $pembelian->save();

            /* COMMIT DB TRANSACTION */
            DB::commit();

            $pembelian = PembelianResource::make($pembelian);
            return $pembelian;
        } catch (Exception $ex) {
            /* ROLLBACK DB TRANSACTION */
            DB::rollback();

            throw $ex;
        }
    }

    /* UPDATE PEMBELIAN */
    public function updatePembelian($props, $id){
        /* BEGIN DB TRANSACTION */
        DB::beginTransaction();

        try {
            $pembelian = $this->pembelianModel::find($id);
            if ($pembelian) {
                /* UPDATE PEMBELIAN */
                $pembelian->tanggal                 = $props['tanggal'];
                $pembelian->nominal                 = $props['nominal'];
                $pembelian->metode_bayar            = $props['metode_bayar'];
                $pembelian->uraian                  = $props['uraian'];
                $pembelian->kode_akun_persediaan    = $props['kode_akun_persediaan'];
                $pembelian->kode_akun_pembayaran    = $props['kode_akun_pembayaran'];
                $pembelian->kode_user               = $this->returnAuthUser()->kode_user;
                $pembelian->update();

                /* COMMIT DB TRANSACTION */
                DB::commit();

                $pembelian = PembelianResource::make($pembelian);
                return $pembelian;
            } else {
                throw new Exception('Catatan tidak ditemukan!');
            }
        } catch (Exception $ex) {
            /* ROLLBACK DB TRANSACTION */
            DB::rollback();

            throw $ex;
        }
    }

    /* DESTROY PEMBELIAN */
    public function destroyPembelian($id){
        try {
            $pembelian = $this->pembelianModel::find($id);
            if ($pembelian) {
                $pembelian->delete();

                return null;
            }

            throw new Exception('Catatan tidak ditemukan!');
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /* DESTROY SELECTED / MULTIPLE PEMBELIAN */
    public function destroyMultiplePembelian($props){
        try {
            $pembelian = $this->pembelianModel::whereIn('id', $props);

            if ($pembelian->count() > 0) {
                $pembelian->delete();

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
        $maxID  = DB::select('SELECT IFNULL(RIGHT(MAX(kode_beli), 5), 0) AS maxID FROM pembelian WHERE deleted_at IS NULL AND RIGHT(LEFT(kode_beli, 7), 4) = :id', ['id' => $year]);
        $newID  = (int)$maxID[0]->maxID + 1;
        $newID  = 'PB-'.$year.''.substr("0000000$newID", -5);

        return $newID;
    }
}
