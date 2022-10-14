<?php
namespace App\Http\Services;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class BaseService
{
    /* RETURN USER APP MODEL */
    public function returnNewUserApp(){
        return new User();
    }

    /* RETURN REQUEST VALIDATION */
    public function returnValidator($props, $rules){
        return Validator::make($props, $rules);
    }

    /* RETURN CARBON */
    public function returnCarbon(){
        return new Carbon;
    }

    /* RETURN HASH */
    public function returnHash(){
        return new Hash;
    }

    /* RETURN STR */
    public function returnStr(){
        return new Str;
    }

    /* RETURN AUTHENTICATED USER */
    public function returnAuthUser(){
        return Auth::user();
    }

    /* RETURN DATE ONLY */
    public function returnDateOnly($props){
        $date = date("Y/m/d", strtotime($props));
        return $date;
    }

    /* DATA FILTER AND RETURN AS A MODEL WITH EXTENDED */
    protected function dataFilter($model, $props, $extenders){
        /* SEARCH IF AVAILABLE */
        if (isset($props['search'])) {
            $model = $model->search($props['search'], null, true, true);
        }

        /* FILTER BY DATE IF AVAILABLE */
        if (isset($props['start'])) {
            if (isset($props['end'])) {
                $model = $model->whereDate($props['date_field'], '>=', $this->returnDateOnly($props['start']))->whereDate($props['date_field'], '<=', $this->returnDateOnly($props['end']));
            } else {
                $model = $model->whereDate($props['date_field'], '=', $this->returnDateOnly($props['start']));
            }
        }

        /* LIMIT RECORDS */
        if (isset($props['take'])) {
            $model = $model->take($props['take']);
        }
        if (isset($props['skip'])) {
            $model = $model->skip($props['skip'] * $props['take']);
        }
        if (isset($props['page'])) {
            $model = $model->skip($props['page'] * $props['take']);
        }

        /* EXTENDED SORT */
        if (isset($extenders)) {
            foreach ($extenders as $item) {
                $model = $model->orderBy($item['field'], $item['option']);
            }
        }

        /* SORT RECORD IF AVAILABLE */
        if (isset($props['sort_option']) && isset($props['sort_field'])) {
            $model = $model->orderBy($props['sort_field'], $props['sort_option']);
        }

        /* RETURN AS A MODEL */
        return $model;
    }

    /* DATA FILTER FOR PAGINATION AND RETURN AS A MODEL WITH EXTENDED */
    protected function dataFilterPagination($model, $props = [], $extenders){
        // dd($props);
        /* SEARCH IF AVAILABLE */
        if (isset($props['search'])) {
            $model = $model->search($props['search'], null, true, true);
        }

        /* FILTER BY DATE IF AVAILABLE */
        if (isset($props['start'])) {
            if (isset($props['end'])) {
                $model = $model->whereDate($props['date_field'], '>=', $this->returnDateOnly($props['start']))->whereDate($props['date_field'], '<=', $this->returnDateOnly($props['end']));
            } else {
                $model = $model->whereDate($props['date_field'], '=', $this->returnDateOnly($props['start']));
            }
        }

        /* EXTENDED SORT */
        if (isset($extenders)) {
            foreach ($extenders as $item) {
                $model = $model->orderBy($item['field'], $item['option']);
            }
        }

        /* SORT RECORD IF AVAILABLE */
        if (isset($props['sort_option']) && isset($props['sort_field'])) {
            $model = $model->orderBy($props['sort_field'], $props['sort_option']);
        }

        /* RETURN AS A MODEL */
        return $model;
    }

    /* UPLOAD FILE FUNCTION */
    protected function returnUploadFile($path, $props)
    {
        try {
            // IF FILE IMAGE NOT EMPTY, THEN UPLOAD IMAGE
            $newName = "";

            /* CREATE VARIABLE WITH FILE AND STORE UPLOADED ORDER FILE */
            $file = $props->file('file');

            /* CHECK PATH IS EXISTS OR NOT */
            /* IF DOES NOT EXISTS, CREATE DIRECTORY */
            if (!File::isDirectory($path)) {
                File::makeDirectory($path, 0777, true, true);
            }

            /* GET ORIGINAL OF ORDER FILE NAME */
            $newName = $file->getClientOriginalName();

            /* IF THE FILE EXISTS ON THE STORAGE, THEN CREATE NEW FILE NAME */
            if (Storage::exists($newName)) {
                $newName = rand() .$file->getClientOriginalName();
            }

            /* DO UPLOAD FILE TO STORAGE */
            $file->move($path, $newName);

            $response = [
                'status'        => 'success',
                'status_code'   => Response::HTTP_CREATED,
                'filename'      => $newName,
                'message'       => 'Image uploaded'
            ];
            return $response;
        } catch (Exception $ex) {
            $response = [
                'status'        => 'error',
                'status_code'   => $ex->getCode(),
                'message'       => 'Unable to upload image. Error Message : ' . $ex->getMessage()
            ];
            return $response;
        }
    }

    /* DELETE FILE FUNCTION */
    protected function returnDeleteFile($path, $filename)
    {
        /* DELETE IMAGE IF EXISTS ON STORAGE */
        $originalPath = $path . '/' . $filename;
        $thumbnailPath = $path . '/thumbnail/' . $filename;

        try {
            File::delete($originalPath);
            File::delete($thumbnailPath);
        } catch (Exception $ex) {
            return $ex->getMessage();
        }
    }
}
