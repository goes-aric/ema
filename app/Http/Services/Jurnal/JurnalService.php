<?php
namespace App\Http\Services\Jurnal;

use Exception;
use App\Models\JurnalUmum;
use App\Http\Services\BaseService;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\Jurnal\JurnalResource;
use App\Models\DetailJurnalUmum;

class JurnalService extends BaseService
{
    /* PRIVATE VARIABLE */
    private $jurnalModel;
    private $detailModel;
    private $carbon;

    public function __construct()
    {
        $this->jurnalModel = new JurnalUmum();
        $this->detailModel = new DetailJurnalUmum();
        $this->carbon = $this->returnCarbon();
    }

    /* FETCH ALL JURNAL */
    public function fetchAll($props){
        /* GET DATA WITH FILTER AS A MODEL */
        $datas = $this->dataFilterPagination($this->jurnalModel, $props, null);

        /* RETRIEVE ALL ROW, CONVERT TO ARRAY AND FORMAT AS RESOURCE */
        $datas = $datas->get();
        $jurnal = JurnalResource::collection($datas);

        return $jurnal;
    }

    /* FETCH ALL JURNAL */
    public function fetchLimit($props){
        /* GET DATA FOR PAGINATION AS A MODEL */
        $getAllData = $this->dataFilterPagination($this->jurnalModel, [], null);
        $totalData = $getAllData->count();

        /* GET DATA WITH FILTER FOR PAGINATION AS A MODEL */
        $getFilterData = $this->dataFilterPagination($this->jurnalModel, $props, null);
        $totalFiltered = $getFilterData->count();

        /* GET DATA WITH FILTER AS A MODEL */
        $datas = $this->dataFilter($this->jurnalModel, $props, null);

        /* RETRIEVE ALL ROW, CONVERT TO ARRAY AND FORMAT AS RESOURCE */
        $datas = $datas->get();
        $datas = JurnalResource::collection($datas);
        $jurnal = [
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

        return $jurnal;
    }

    /* FETCH JURNAL BY ID */
    public function fetchById($id){
        try {
            $jurnal = $this->jurnalModel::find($id);
            if ($jurnal) {
                $jurnal = JurnalResource::make($jurnal);
                return $jurnal;
            }

            throw new Exception('Catatan tidak ditemukan!');
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /* CREATE NEW JURNAL */
    public function createJurnal($props){
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
            $newName = 'jurnal-'.$this->carbon::now().'.'. $image->getClientOriginalExtension();
            $uploadImage = $this->returnUploadFile($imagePath, $newName, $imageBinary);
            if ($uploadImage['status'] == 'success') {
                $imageName = $uploadImage['filename'];
            }

            /* GENERATE NEW ID */
            $newID = $this->createNoJurnal();

            $jurnal = $this->jurnalModel;
            $jurnal->no_jurnal          = $newID;
            $jurnal->tanggal_transaksi  = $props['tanggal_transaksi'];
            $jurnal->deskripsi          = $props['deskripsi'];
            $jurnal->sumber             = $props['sumber'];
            $jurnal->gambar             = $imageName;
            $jurnal->kode_user          = $this->returnAuthUser()->kode_user;
            $jurnal->save();

            /* REMOVE PREV DETAILS */
            $this->detailModel::where('no_jurnal', '=', $jurnal['no_jurnal'])->delete();

            /* DETAILS */
            foreach (json_decode($props['jurnal']) as $item) {
                $detail = new $this->detailModel;
                $detail->no_jurnal  = $jurnal['no_jurnal'];
                $detail->kode_akun  = $item->kode_akun;
                $detail->nama_akun  = $item->nama_akun;
                $detail->debet      = $item->debet;
                $detail->kredit     = $item->kredit;
                $detail->save();
            }

            /* COMMIT DB TRANSACTION */
            DB::commit();

            $jurnal = JurnalResource::make($jurnal);
            return $jurnal;
        } catch (Exception $ex) {
            /* ROLLBACK DB TRANSACTION */
            DB::rollback();

            throw $ex;
        }
    }

    /* UPDATE JURNAL */
    public function updateJurnal($props, $id){
        /* BEGIN DB TRANSACTION */
        DB::beginTransaction();

        try {
            $jurnal = $this->jurnalModel::find($id);
            if ($jurnal) {
                /* IMAGE VARIABLE */
                $imageName = $jurnal->gambar;
                $imagePath = storage_path("app/public/images/");
                $imageBinary = $props->file('gambar');

                /* TRY TO UPLOAD IMAGE */
                if (!empty($props->file('gambar'))) {
                    // IF CURRENT IMAGE IS NOT EMPTY, DELETE CURRENT IMAGE
                    if ($jurnal->gambar != null) {
                        $this->returnDeleteFile($imagePath, $imageName);
                    }

                    /* DECLARE NEW IMAGE VARIABLE */
                    $image = $props->file('gambar');
                    $newName = 'jurnal-'.$this->carbon::now().'.'. $image->getClientOriginalExtension();
                    $uploadImage = $this->returnUploadFile($imagePath, $newName, $imageBinary);
                    if ($uploadImage['status'] == 'success') {
                        $imageName = $uploadImage['filename'];
                    }
                }

                /* UPDATE JURNAL */
                $jurnal->tanggal_transaksi  = $props['tanggal_transaksi'];
                $jurnal->deskripsi          = $props['deskripsi'];
                $jurnal->sumber             = $props['sumber'];
                $jurnal->gambar             = $imageName;
                $jurnal->kode_user          = $this->returnAuthUser()->kode_user;
                $jurnal->update();

                /* REMOVE PREV DETAILS */
                $this->detailModel::where('no_jurnal', '=', $jurnal['no_jurnal'])->delete();

                /* DETAILS */
                foreach (json_decode($props['jurnal']) as $item) {
                    $detail = new $this->detailModel;
                    $detail->no_jurnal  = $jurnal['no_jurnal'];
                    $detail->kode_akun  = $item->kode_akun;
                    $detail->nama_akun  = $item->nama_akun;
                    $detail->debet      = $item->debet;
                    $detail->kredit     = $item->kredit;
                    $detail->save();
                }

                /* COMMIT DB TRANSACTION */
                DB::commit();

                $jurnal = JurnalResource::make($jurnal);
                return $jurnal;
            } else {
                throw new Exception('Catatan tidak ditemukan!');
            }
        } catch (Exception $ex) {
            /* ROLLBACK DB TRANSACTION */
            DB::rollback();

            throw $ex;
        }
    }

    /* DESTROY JURNAL */
    public function destroyJurnal($id){
        try {
            $jurnal = $this->jurnalModel::find($id);
            if ($jurnal) {
                $jurnal->delete();

                return null;
            }

            throw new Exception('Catatan tidak ditemukan!');
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /* DESTROY SELECTED / MULTIPLE JURNAL */
    public function destroyMultipleJurnal($props){
        try {
            $jurnal = $this->jurnalModel::whereIn('id', $props);

            if ($jurnal->count() > 0) {
                $jurnal->delete();

                return null;
            }

            throw new Exception('Catatan tidak ditemukan!');
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /* GENERATE NO JURNAL AUTOMATICALLY */
    public function createNoJurnal(){
        $year   = $this->carbon::now()->format('Y');

        $newID  = "";
        $maxID  = DB::select('SELECT IFNULL(RIGHT(MAX(no_jurnal), 5), 0) AS maxID FROM jurnal_umum WHERE deleted_at IS NULL AND RIGHT(LEFT(no_jurnal, 7), 4) = :id', ['id' => $year]);
        $newID  = (int)$maxID[0]->maxID + 1;
        $newID  = 'JU-'.$year.''.substr("0000000$newID", -5);

        return $newID;
    }
}
