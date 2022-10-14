<?php
namespace App\Http\Resources\Pembelian;

use Illuminate\Http\Resources\Json\JsonResource;

class PembelianResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'                    => $this->id,
            'kode_beli'             => $this->kode_beli,
            'tanggal'               => $this->tanggal,
            'nominal'               => $this->nominal,
            'metode_bayar'          => $this->metode_bayar,
            'uraian'                => $this->uraian,
            'kode_akun_persediaan'  => $this->kode_akun_persediaan,
            'nama_akun_persediaan'  => $this->akunPersediaan->nama_akun,
            'kode_akun_pembayaran'  => $this->kode_akun_pembayaran,
            'nama_akun_pembayaran'  => $this->akunPembayaran->nama_akun,
            'kode_user'             => $this->createdUser->kode_user,
            'nama_user'             => $this->createdUser->nama ?? null
        ];
    }
}
