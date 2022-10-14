<?php
namespace App\Http\Resources\Penjualan;

use Illuminate\Http\Resources\Json\JsonResource;

class PenjualanResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'                    => $this->id,
            'kode_jual'             => $this->kode_jual,
            'tanggal'               => $this->tanggal,
            'nominal'               => $this->nominal,
            'uraian'                => $this->uraian,
            'kode_akun_persediaan'  => $this->kode_akun_persediaan,
            'nama_akun_persediaan'  => $this->akunPersediaan->nama_akun,
            'kode_akun_penerimaan'  => $this->kode_akun_penerimaan,
            'nama_akun_penerimaan'  => $this->akunPenerimaan->nama_akun,
            'kode_user'             => $this->createdUser->kode_user,
            'nama_user'             => $this->createdUser->nama ?? null
        ];
    }
}
