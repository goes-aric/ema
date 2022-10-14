<?php
namespace App\Http\Resources\Akun;

use Illuminate\Http\Resources\Json\JsonResource;

class AkunResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'            => $this->id,
            'kode_akun'     => $this->kode_akun,
            'nama_akun'     => $this->nama_akun,
            'akun_utama'    => $this->akun_utama,
            'tipe_akun'     => $this->tipe_akun,
            'kode_user'     => $this->createdUser->kode_user,
            'nama_user'     => $this->createdUser->nama ?? null
        ];
    }
}
