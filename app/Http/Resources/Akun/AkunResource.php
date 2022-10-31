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
            'induk'         => $this->akunInduk ?? null,
            'tipe_akun_id'  => $this->akunArray($this->tipe_akun),
            'tipe_akun'     => $this->tipe_akun,
            'kode_user'     => $this->createdUser->kode_user,
            'nama_user'     => $this->createdUser->nama ?? null
        ];
    }

    public function akunArray($name){
        $akun = [
            [
                'id'    => '1-',
                'name'  => 'AKTIVA'
            ],
            [
                'id'    => '2-',
                'name'  => 'KEWAJIBAN'
            ],
            [
                'id'    => '3-',
                'name'  => 'EKUITAS'
            ],
            [
                'id'    => '4-',
                'name'  => 'PENDAPATAN'
            ],
            [
                'id'    => '5-',
                'name'  => 'BEBAN'
            ]
        ];

        $key = array_search($name, array_column($akun, 'name'));
        return $akun[$key]['id'];
    }
}
