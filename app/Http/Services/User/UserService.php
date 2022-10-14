<?php
namespace App\Http\Services\User;

use Exception;
use App\Http\Services\BaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\User\UserResource;

class UserService extends BaseService
{
    /* PRIVATE VARIABLE */
    private $model;
    private $carbon;
    private $hash;

    public function __construct()
    {
        $this->model = $this->returnNewUserApp();
        $this->carbon = $this->returnCarbon();
        $this->hash = $this->returnHash();
        $this->str = $this->returnStr();
    }

    /* FETCH ALL USER */
    public function fetchLimit($props){
        /* GET DATA FOR PAGINATION AS A MODEL */
        $getAllData = $this->dataFilterPagination($this->model, [], null);
        $totalData = $getAllData->count();

        /* GET DATA WITH FILTER FOR PAGINATION AS A MODEL */
        $getFilterData = $this->dataFilterPagination($this->model, $props, null);
        $totalFiltered = $getFilterData->count();

        /* GET DATA WITH FILTER AS A MODEL */
        $datas = $this->dataFilter($this->model, $props, null);

        /* RETRIEVE ALL ROW, CONVERT TO ARRAY AND FORMAT AS RESOURCE */
        $datas = $datas->get();
        $datas = UserResource::collection($datas);
        $users = [
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

        return $users;
    }

    /* FETCH USER BY ID */
    public function fetchById($id){
        try {
            $user = $this->model::find($id);
            if ($user) {
                $user = UserResource::make($user);
                return $user;
            }

            throw new Exception('Catatan tidak ditemukan!');
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /* CREATE NEW USER */
    public function createUser($props){
        /* BEGIN DB TRANSACTION */
        DB::beginTransaction();

        try {
            $newID = $this->createKodeUser();

            $user = $this->returnNewUserApp();
            $user->kode_user    = $newID;
            $user->nama         = $props['nama'];
            $user->alamat       = $props['alamat'];
            $user->no_telp      = $props['no_telp'];
            $user->email        = $props['email'];
            $user->username     = $props['username'];
            $user->password     = bcrypt($props['password']);
            $user->hak_akses    = $props['hak_akses'];
            $user->save();

            /* COMMIT DB TRANSACTION */
            DB::commit();

            $user = UserResource::make($user);
            return $user;
        } catch (Exception $ex) {
            /* ROLLBACK DB TRANSACTION */
            DB::rollback();

            throw $ex;
        }
    }

    /* UPDATE USER */
    public function updateUser($props, $id){
        /* BEGIN DB TRANSACTION */
        DB::beginTransaction();

        try {
            $user = $this->model::find($id);
            if ($user) {
                /* UPDATE USER */
                $user->nama         = $props['nama'];
                $user->alamat       = $props['alamat'];
                $user->no_telp      = $props['no_telp'];
                $user->email        = $props['email'];
                $user->username     = $props['username'];
                $user->hak_akses    = $props['hak_akses'];
                $user->update();

                /* COMMIT DB TRANSACTION */
                DB::commit();

                $user = UserResource::make($user);
                return $user;
            } else {
                throw new Exception('Catatan tidak ditemukan!');
            }
        } catch (Exception $ex) {
            /* ROLLBACK DB TRANSACTION */
            DB::rollback();

            throw $ex;
        }
    }

    /* UPDATE PROFILE USER */
    public function updateProfile($props){
        /* BEGIN DB TRANSACTION */
        DB::beginTransaction();

        /* GET LOGGED IN USER */
        $userId = $this->returnAuthUser()->id;

        try {
            $this->oldValues = $this->model::find($userId);
            $user = $this->model::find($userId);
            if ($user) {
                /* UPDATE USER */
                $user->nama         = $props['nama'];
                $user->alamat       = $props['alamat'];
                $user->no_telp      = $props['no_telp'];
                $user->username     = $props['username'];
                $user->email        = $props['email'];
                $user->update();

                /* COMMIT DB TRANSACTION */
                DB::commit();

                $user = UserResource::make($user);
                return $user;
            } else {
                throw new Exception('Catatan tidak ditemukan!');
            }
        } catch (Exception $ex) {
            /* ROLLBACK DB TRANSACTION */
            DB::rollback();

            throw $ex;
        }
    }

    /* UPDATE CURRENT / SIGNED USER PASSWORD */
    public function updatePassword($props){
        try {
            $authUser = $this->returnAuthUser();
            $user = $this->model::find($authUser->id);

            // CHECK OLD PASSWORD WITH CURRENT PASSWORD
            if ($this->hash::check($props['current_password'], $user->password)){
                if ($this->hash::check($props['password'], $user->password)){
                    // UPDATE PASSWORD DENIED DUE TO CURRENT PASSWORD MATCH WITH OLD PASSWORD
                    throw new Exception('Password baru tidak boleh sama dengan password saat ini!');
                }else{
                    // UPDATE PASSWORD
                    $user->password   = bcrypt($props['password']);
                    $user->update();

                    return $user;
                }
            }else{
                throw new Exception('Password anda saat ini tidak cocok. Memperbaharui password dibatalkan!');
            }
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /* DESTROY USER */
    public function destroyUser($id){
        try {
            if ((int)$id !== (int)$this->returnAuthUser()->id) {
                $user = $this->model::find($id);
                if ($user) {
                    $user->delete();

                    return null;
                }

                throw new Exception('Catatan tidak ditemukan!');
            }

            throw new Exception("Permintaan pemrosesan gagal", 1);
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /* DESTROY SELECTED / MULTIPLE USER */
    public function destroyMultipleUser($props){
        try {
            $users = $this->model::whereIn('id', $props);

            if ($users->count() > 0) {
                $users->delete();

                return null;
            }

            throw new Exception('Catatan tidak ditemukan!');
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /* LOGIN FUNCTION AND GENERATE ACCESS TOKEN */
    public function authUser($props){
        try {
            /* DEFINE INPUT VARIABLE */
            $username   = $props['username'];
            $password   = $props['password'];

            /* CHECK LOGIN METHOD AND STORE FIELD METHOD TO VARIABLE */
            $usernameField = "username";
            $passwordField = "password";
            if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
                $usernameField = "email";
            }

            /* RUN AUTH ATTEMPT */
            if (Auth::attempt([$usernameField => $username, $passwordField => $password])) {
                $user = $this->returnAuthUser();
                $token = $user->createToken('myToken')->accessToken;

                $user = UserResource::make($user);
                $responseData = [
                    'data'  => $user,
                    'token' => $token
                ];
                return $responseData;
            } else {
                throw new Exception('Username atau Password salah');
            }
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /* LOGOUT FUNCTION AND REVOKE ACCESS TOKEN */
    public function logoutUser(){
        try {
            // REVOKE TOKEN
            $user = $this->returnAuthUser();
            $user->token()->revoke();

            return null;
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /* REGISTER A USER */
    public function registerUser($props){
        /* BEGIN DB TRANSACTION */
        DB::beginTransaction();

        try {
            $newID = $this->createKodeUser();

            $user = $this->returnNewUserApp();
            $user->kode_user    = $newID;
            $user->nama         = $props['nama'];
            $user->alamat       = $props['alamat'];
            $user->no_telp      = $props['no_telp'];
            $user->username     = $props['username'];
            $user->email        = $props['email'];
            $user->password     = bcrypt($props['password']);
            $user->hak_akses    = $props['hak_akses'];
            $user->save();

            /* COMMIT DB TRANSACTION */
            DB::commit();

            return $user;
        } catch (Exception $ex) {
            /* ROLLBACK DB TRANSACTION */
            DB::rollback();

            throw $ex;
        }
    }

    /* FETCH ALL USER FOR OPTIONS */
    public function fetchDataOptions($props){
        try {
            /* GET DATA WITH FILTER AS A MODEL */
            $datas = $this->dataFilterPagination($this->model, $props, null);

            /* RETRIEVE ALL ROW, CONVERT TO ARRAY AND FORMAT AS RESOURCE */
            $users = $datas->select('id', 'kode_user', 'nama')->get();

            return $users;
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /* GET LOGGED IN USER */
    public function getAuthUserId(){
        $userId = $this->returnAuthUser()->id;
        return $userId;
    }

    /* GENERATE KODE USER AUTOMATICALLY */
    public function createKodeUser(){
        $newID  = "";
        $maxID  = DB::select('SELECT IFNULL(RIGHT(MAX(kode_user), 3), 0) AS maxID FROM users');
        $newID  = (int)$maxID[0]->maxID + 1;
        $newID  = 'A-'.substr("0000000$newID", -3);

        return $newID;
    }
}
