<?php
namespace App\Http\Controllers\User;

use Exception;
use Illuminate\Http\Request;
use App\Http\Services\User\UserService;
use App\Http\Controllers\BaseController;
use Illuminate\Validation\Rules\Password;

class UserController extends BaseController
{
    private $userServices;
    private $moduleName;

    public function __construct(UserService $userServices)
    {
        $this->userServices = $userServices;
        $this->moduleName = 'User';
    }

    public function index(Request $request)
    {
        try {
            $props = $this->getBaseQueryParams($request, []);
            $users = $this->userServices->fetchLimit($props);

            return $this->returnResponse('success', self::HTTP_OK, 'Daftar user', $users);
        } catch (Exception $ex) {
            return $this->returnExceptionResponse('error', self::HTTP_BAD_REQUEST, $ex);
        }
    }

    public function store(Request $request)
    {
        try {
            $rules = [
                'nama'			        => 'required|string|max:255',
                'alamat'                => 'nullable',
                'no_telp'               => 'nullable',
                'username'		        => 'required|string|max:255|alpha_dash|unique:users',
                'email'			        => 'required|string|email|max:255|unique:users',
                'password'              => [
                    'required', 'confirmed', Password::min(6)->letters()->mixedCase()->numbers()->symbols()->uncompromised()
                ],
                'password_confirmation'	=> [
                    'required', Password::min(6)->letters()->mixedCase()->numbers()->symbols()->uncompromised()
                ],
                'hak_akses'             => 'required',
            ];
            $validator = $this->returnValidator($request->all(), $rules);
            if ($validator->fails()) {
                return $this->returnResponse('error', self::HTTP_UNPROCESSABLE_ENTITY, $validator->errors());
            }

            $user = $this->userServices->createUser($request);
            return $this->returnResponse('success', self::HTTP_CREATED, 'User berhasil dibuat', $user);
        } catch (Exception $ex) {
            return $this->returnExceptionResponse('error', self::HTTP_BAD_REQUEST, $ex);
        }
    }

    public function show($id)
    {
        try {
            $user = $this->userServices->fetchById($id);
            return $this->returnResponse('success', self::HTTP_OK, 'Detail user', $user);
        } catch (Exception $ex) {
            return $this->returnExceptionResponse('error', self::HTTP_BAD_REQUEST, $ex);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $rules = [
                'nama'      => 'required|string|max:255',
                'alamat'    => 'nullable',
                'no_telp'   => 'nullable',
                'username'  => 'required|string|max:255|alpha_dash|unique:users,username,'.$id.'',
                'email'     => 'required|email|max:255|unique:users,email,'.$id.'',
                'hak_akses' => 'required',
            ];
            $validator = $this->returnValidator($request->all(), $rules);
            if ($validator->fails()) {
                return $this->returnResponse('error', self::HTTP_UNPROCESSABLE_ENTITY, $validator->errors());
            }

            $user = $this->userServices->updateUser($request, $id);
            return $this->returnResponse('success', self::HTTP_OK, 'Data user berhasil diperbaharui', $user);
        } catch (Exception $ex) {
            return $this->returnExceptionResponse('error', self::HTTP_BAD_REQUEST, $ex);
        }
    }

    public function updateProfile(Request $request)
    {
        try {
            $userId = $this->userServices->getAuthUserId();

            $rules = [
                'nama'      => 'required|string|max:255',
                'alamat'    => 'nullable',
                'no_telp'   => 'nullable',
                'username'  => 'required|string|max:255|alpha_dash|unique:users,username,'.$userId.'',
                'email'     => 'required|email|max:255|unique:users,email,'.$userId.'',
            ];
            $validator = $this->returnValidator($request->all(), $rules);
            if ($validator->fails()) {
                return $this->returnResponse('error', self::HTTP_UNPROCESSABLE_ENTITY, $validator->errors());
            }

            $user = $this->userServices->updateProfile($request);
            return $this->returnResponse('success', self::HTTP_OK, 'Profil berhasil diperbaharui', $user);
        } catch (Exception $ex) {
            return $this->returnExceptionResponse('error', self::HTTP_BAD_REQUEST, $ex);
        }
    }

    public function changePassword(Request $request)
    {
        try {
            $rules = [
                'current_password'      => 'required|string|min:6',
                'password'		        => [
                    'required', 'confirmed', Password::min(6)->letters()->mixedCase()->numbers()->symbols()->uncompromised()
                ],
                'password_confirmation'	=> [
                    'required', Password::min(6)->letters()->mixedCase()->numbers()->symbols()->uncompromised()
                ]
            ];
            $validator = $this->returnValidator($request->all(), $rules);
            if ($validator->fails()) {
                return $this->returnResponse('error', self::HTTP_UNPROCESSABLE_ENTITY, $validator->errors());
            }

            $user = $this->userServices->updatePassword($request);
            return $this->returnResponse('success', self::HTTP_OK, 'Password berhasil diperbaharui', $user);
        } catch (Exception $ex) {
            return $this->returnExceptionResponse('error', self::HTTP_BAD_REQUEST, $ex);
        }
    }

    public function destroy($id)
    {
        try {
            $user = $this->userServices->destroyUser($id);
            return $this->returnResponse('success', self::HTTP_OK, 'User berhasil dihapus!', $user);
        } catch (Exception $ex) {
            return $this->returnExceptionResponse('error', self::HTTP_BAD_REQUEST, $ex);
        }
    }

    public function destroyMultiple(Request $request)
    {
        try {
            $props = $request->data;
            $users = $this->userServices->destroyMultipleUser($props);

            return $this->returnResponse('success', self::HTTP_OK, 'User berhasil dihapus!', $users);
        } catch (Exception $ex) {
            return $this->returnExceptionResponse('error', self::HTTP_BAD_REQUEST, $ex);
        }
    }

    public function fetchDataOptions(Request $request)
    {
        try {
            $props = $this->getBaseQueryParams($request, []);
            $users = $this->userServices->fetchDataOptions($props);

            return $this->returnResponse('success', self::HTTP_OK, 'Daftar user', $users);
        } catch (Exception $ex) {
            return $this->returnExceptionResponse('error', self::HTTP_BAD_REQUEST, $ex);
        }
    }
}
