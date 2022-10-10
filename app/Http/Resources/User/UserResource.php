<?php
namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'        => $this->id,
            'kode_user' => $this->kode_user,
            'nama'      => ucwords(strtolower($this->nama)),
            'alamat'    => $this->alamat,
            'no_telp'   => $this->no_telp,
            'username'  => $this->username,
            'email'     => $this->email,
            'hak_akses' => $this->is_admin
        ];
    }
}
