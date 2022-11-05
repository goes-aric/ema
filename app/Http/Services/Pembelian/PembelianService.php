<?php
namespace App\Http\Services\Pembelian;

use Exception;
use App\Models\Akun;
use App\Models\Pembelian;
use App\Models\JurnalUmum;
use App\Models\DetailJurnalUmum;
use App\Http\Services\BaseService;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\Pembelian\PembelianResource;
use App\Http\Services\Jurnal\JurnalService;

class PembelianService extends BaseService
{
    /* PRIVATE VARIABLE */
    private $pembelianModel;
    private $jurnalModel;
    private $detailModel;
    private $akunModel;
    private $jurnalService;
    private $carbon;

    public function __construct()
    {
        $this->pembelianModel = new Pembelian();
        $this->jurnalModel = new JurnalUmum();
        $this->detailModel = new DetailJurnalUmum();
        $this->akunModel = new Akun();
        $this->jurnalService = new JurnalService;
        $this->carbon = $this->returnCarbon();
    }

    /* FETCH ALL PEMBELIAN */
    public function fetchAll($props){
        /* GET DATA WITH FILTER AS A MODEL */
        $datas = $this->dataFilterPagination($this->pembelianModel, $props, null);

        /* RETRIEVE ALL ROW, CONVERT TO ARRAY AND FORMAT AS RESOURCE */
        $datas = $datas->get();
        $pembelian = PembelianResource::collection($datas);

        return $pembelian;
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
            /* IMAGE VARIABLE */
            $imageName = null;
            $imagePath = storage_path("app/public/images/");
            $imageBinary = $props->file('gambar');

            /* TRY TO UPLOAD IMAGE FIRST */
            /* DECLARE NEW IMAGE VARIABLE */
            $image = $props->file('gambar');
            $newName = 'pembelian-'.$this->carbon::now().'.'. $image->getClientOriginalExtension();
            $uploadImage = $this->returnUploadFile($imagePath, $newName, $imageBinary);
            if ($uploadImage['status'] == 'success') {
                $imageName = $uploadImage['filename'];
            }

            /* GENERATE NEW ID */
            $newID = $this->createNoTransaksi();

            $pembelian = $this->pembelianModel;
            $pembelian->kode_beli               = $newID;
            $pembelian->tanggal                 = $props['tanggal'];
            $pembelian->nominal                 = $props['nominal'];
            $pembelian->metode_bayar            = $props['metode_bayar'];
            $pembelian->uraian                  = $props['uraian'];
            $pembelian->kode_akun_persediaan    = $props['kode_akun_persediaan'];
            $pembelian->kode_akun_pembayaran    = $props['kode_akun_pembayaran'];
            $pembelian->gambar                  = $imageName;
            $pembelian->kode_user               = $this->returnAuthUser()->kode_user;
            $pembelian->save();

            /* GET NO JURNAL */
            $noJurnal = $this->jurnalService->createNoJurnal();

            /* CREATE JURNAL */
            $jurnal = new $this->jurnalModel;
            $jurnal->no_jurnal          = $noJurnal;
            $jurnal->tanggal_transaksi  = $props['tanggal'];
            $jurnal->deskripsi          = $props['uraian'];
            $jurnal->sumber             = $newID;
            $jurnal->kode_user          = $this->returnAuthUser()->kode_user;
            $jurnal->save();

            /* PERSEDIAAN */
            $akunPersediaan = $this->akunModel::where('kode_akun', '=', $props['kode_akun_persediaan'])->first();

            $persediaan = new $this->detailModel;
            $persediaan->no_jurnal  = $jurnal['no_jurnal'];
            $persediaan->kode_akun  = $akunPersediaan->kode_akun;
            $persediaan->nama_akun  = $akunPersediaan->nama_akun;
            $persediaan->debet      = $props->nominal;
            $persediaan->kredit     = 0;
            $persediaan->save();

            /* PEMBAYARAN */
            $akunPembayaran = $this->akunModel::where('kode_akun', '=', $props['kode_akun_pembayaran'])->first();

            $pembayaran = new $this->detailModel;
            $pembayaran->no_jurnal  = $jurnal['no_jurnal'];
            $pembayaran->kode_akun  = $akunPembayaran->kode_akun;
            $pembayaran->nama_akun  = $akunPembayaran->nama_akun;
            $pembayaran->debet      = 0;
            $pembayaran->kredit     = $props->nominal;
            $pembayaran->save();

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
                /* IMAGE VARIABLE */
                $imageName = $pembelian->gambar;
                $imagePath = storage_path("app/public/images/");
                $imageBinary = $props->file('gambar');

                /* TRY TO UPLOAD IMAGE */
                if (!empty($props->file('gambar'))) {
                    // IF CURRENT IMAGE IS NOT EMPTY, DELETE CURRENT IMAGE
                    if ($pembelian->gambar != null) {
                        $this->returnDeleteFile($imagePath, $imageName);
                    }

                    /* DECLARE NEW IMAGE VARIABLE */
                    $image = $props->file('gambar');
                    $newName = 'pembelian-'.$this->carbon::now().'.'. $image->getClientOriginalExtension();
                    $uploadImage = $this->returnUploadFile($imagePath, $newName, $imageBinary);
                    if ($uploadImage['status'] == 'success') {
                        $imageName = $uploadImage['filename'];
                    }
                }

                /* UPDATE PEMBELIAN */
                $pembelian->tanggal                 = $props['tanggal'];
                $pembelian->nominal                 = $props['nominal'];
                $pembelian->metode_bayar            = $props['metode_bayar'];
                $pembelian->uraian                  = $props['uraian'];
                $pembelian->kode_akun_persediaan    = $props['kode_akun_persediaan'];
                $pembelian->kode_akun_pembayaran    = $props['kode_akun_pembayaran'];
                $pembelian->gambar                  = $imageName;
                $pembelian->kode_user               = $this->returnAuthUser()->kode_user;
                $pembelian->update();

                /* REMOVE PREV JURNAL */
                $this->jurnalModel::where('sumber', '=', $pembelian->kode_beli)->delete();

                /* GET NO JURNAL */
                $noJurnal = $this->jurnalService->createNoJurnal();

                /* CREATE JURNAL */
                $jurnal = new $this->jurnalModel;
                $jurnal->no_jurnal          = $noJurnal;
                $jurnal->tanggal_transaksi  = $props['tanggal'];
                $jurnal->deskripsi          = $props['uraian'];
                $jurnal->sumber             = $pembelian['kode_beli'];
                $jurnal->kode_user          = $this->returnAuthUser()->kode_user;
                $jurnal->save();

                /* PERSEDIAAN */
                $akunPersediaan = $this->akunModel::where('kode_akun', '=', $props['kode_akun_persediaan'])->first();

                $persediaan = new $this->detailModel;
                $persediaan->no_jurnal  = $jurnal['no_jurnal'];
                $persediaan->kode_akun  = $akunPersediaan->kode_akun;
                $persediaan->nama_akun  = $akunPersediaan->nama_akun;
                $persediaan->debet      = $props->nominal;
                $persediaan->kredit     = 0;
                $persediaan->save();

                /* PEMBAYARAN */
                $akunPembayaran = $this->akunModel::where('kode_akun', '=', $props['kode_akun_pembayaran'])->first();

                $pembayaran = new $this->detailModel;
                $pembayaran->no_jurnal  = $jurnal['no_jurnal'];
                $pembayaran->kode_akun  = $akunPembayaran->kode_akun;
                $pembayaran->nama_akun  = $akunPembayaran->nama_akun;
                $pembayaran->debet      = 0;
                $pembayaran->kredit     = $props->nominal;
                $pembayaran->save();

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
                /* DELETE JURNAL UMUM */
                $this->jurnalModel::where('sumber', '=', $pembelian->kode_beli)->delete();

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

    /* CHARTS PEMBELIAN */
    public function charts(){
        try {
            $pendapatan = [];
            $year = $this->carbon::now()->format('Y');
            $data = [];
            for ($x=1; $x <= 12; $x++) {
                $data[] = $this->pembelianModel::selectRaw("$x AS month, IFNULL(SUM(nominal), 0) AS amount, 'Rupiah' AS unit")
                            ->whereYear('tanggal', '=', $year)
                            ->whereMonth('tanggal', '=', $x)
                            ->first();
            }

            $pendapatan[] = [
                'name'  => $year,
                'data'  => $data
            ];
            return $pendapatan;
        } catch (Exception $ex) {
            throw $ex;
        }
    }
}
