<?php
namespace App\Http\Services;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Str;
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

    /* DATA FILTER AND RETURN AS A MODEL */
    protected function dataFilter($model, $props){
        /* SEARCH IF AVAILABLE */
        if ($props['search'] != null) {
            $model = $model->search($props['search'], null, true, true);
        }

        /* FILTER BY DATE IF AVAILABLE */
        if ($props['start'] != null) {
            if ($props['end'] != null) {
                $model = $model->whereDate($props['date_field'], '>=', $this->returnDateOnly($props['start']))->whereDate($props['date_field'], '<=', $this->returnDateOnly($props['end']));
            } else {
                $model = $model->whereDate($props['date_field'], '=', $this->returnDateOnly($props['start']));
            }
        }

        /* LIMIT RECORDS */
        if ($props['take'] != null) {
            $model = $model->take($props['take']);
        }
        if (isset($props['skip'])) {
            $model = $model->skip($props['skip'] * $props['take']);
        }
        if (isset($props['page'])) {
            $model = $model->skip($props['page'] * $props['take']);
        }

        /* SORT RECORD IF AVAILABLE */
        if ($props['sort_option'] != null && $props['sort_field']) {
            $model = $model->orderBy($props['sort_field'], $props['sort_option']);
        }

        /* RETURN AS A MODEL */
        return $model;
    }

    /* DATA FILTER AND RETURN AS A MODEL (EXTENDED) */
    protected function dataFilterExtended($model, $props, $extenders){
        /* SEARCH IF AVAILABLE */
        if ($props['search'] != null) {
            $model = $model->search($props['search'], null, true, true);
        }

        /* FILTER BY DATE IF AVAILABLE */
        if ($props['start'] != null) {
            if ($props['end'] != null) {
                $model = $model->whereDate($props['date_field'], '>=', $this->returnDateOnly($props['start']))->whereDate($props['date_field'], '<=', $this->returnDateOnly($props['end']));
            } else {
                $model = $model->whereDate($props['date_field'], '=', $this->returnDateOnly($props['start']));
            }
        }

        /* LIMIT RECORDS */
        if ($props['take'] != null) {
            $model = $model->take($props['take']);
        }
        if (isset($props['skip'])) {
            $model = $model->skip($props['skip'] * $props['take']);
        }
        if (isset($props['page'])) {
            $model = $model->skip($props['page'] * $props['take']);
        }

        /* EXTENDED SORT */
        foreach ($extenders as $item) {
            $model = $model->orderBy($item['field'], $item['option']);
        }

        /* SORT RECORD IF AVAILABLE */
        if ($props['sort_option'] != null && $props['sort_field']) {
            $model = $model->orderBy($props['sort_field'], $props['sort_option']);
        }

        /* RETURN AS A MODEL */
        return $model;
    }

    /* DATA FILTER FOR PAGINATION AND RETURN AS A MODEL */
    protected function dataFilterPagination($model, $props = []){
        /* SEARCH IF AVAILABLE */
        if ($props['search'] != null) {
            $model = $model->search($props['search'], null, true, true);
        }

        /* FILTER BY DATE IF AVAILABLE */
        if ($props['start'] != null) {
            if ($props['end'] != null) {
                $model = $model->whereDate($props['date_field'], '>=', $this->returnDateOnly($props['start']))->whereDate($props['date_field'], '<=', $this->returnDateOnly($props['end']));
            } else {
                $model = $model->whereDate($props['date_field'], '=', $this->returnDateOnly($props['start']));
            }
        }

        /* SORT RECORD IF AVAILABLE */
        if ($props['sort_option'] != null && $props['sort_field']) {
            $model = $model->orderBy($props['sort_field'], $props['sort_option']);
        }

        /* RETURN AS A MODEL */
        return $model;
    }

    /* DATA FILTER FOR PAGINATION AND RETURN AS A MODEL (EXTENDED) */
    protected function dataFilterPaginationExtended($model, $props = [], $extenders){
        /* SEARCH IF AVAILABLE */
        if ($props['search'] != null) {
            $model = $model->search($props['search'], null, true, true);
        }

        /* FILTER BY DATE IF AVAILABLE */
        if ($props['start'] != null) {
            if ($props['end'] != null) {
                $model = $model->whereDate($props['date_field'], '>=', $props['start'])->whereDate($props['date_field'], '<=', $props['end']);
            } else {
                $model = $model->whereDate($props['date_field'], '=', $props['start']);
            }
        }

        /* EXTENDED SORT */
        foreach ($extenders as $item) {
            $model = $model->orderBy($item['field'], $item['option']);
        }

        /* SORT RECORD IF AVAILABLE */
        if ($props['sort_option'] != null && $props['sort_field']) {
            $model = $model->orderBy($props['sort_field'], $props['sort_option']);
        }

        /* RETURN AS A MODEL */
        return $model;
    }

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
                'status_code'   => 201,
                'filename'      => $newName,
                'message'       => 'Image uploaded'
            ];
            return $response;
        } catch (\Exception $ex) {
            $response = [
                'status'        => 'error',
                'status_code'   => $ex->getCode(),
                'message'       => 'Unable to upload image. Error Message : ' . $ex->getMessage()
            ];
            return $response;
        }
    }

    protected function returnDeleteFile($path, $filename)
    {
        /* DELETE IMAGE IF EXISTS ON STORAGE */
        $path = $path . '/' . $filename;
        if(Storage::exists($path)){
            File::delete($path);
        }
    }

    protected function returnCopyFile($path, $from, $to)
    {
        /* DELETE IMAGE IF EXISTS ON STORAGE */
        $fromPath = $path . '/' . $from;
        $toPath = $path . '/' . $to;

        if(Storage::exists($path)){
            File::copy($fromPath, $toPath);
        }
    }
}
