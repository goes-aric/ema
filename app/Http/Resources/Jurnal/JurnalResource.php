<?php
namespace App\Http\Resources\Jurnal;

use Illuminate\Http\Resources\Json\JsonResource;

class JurnalResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'                => $this->id,
            'no_jurnal'         => $this->no_jurnal,
            'tanggal_transaksi' => $this->tanggal_transaksi,
            'deskripsi'         => $this->deskripsi,
            'sumber'            => $this->sumber,
            'gambar'            => $this->gambar ? asset('/storage/images') . '/' . $this->gambar : null,
            'details'           => $this->details,
            'kode_user'         => $this->createdUser->kode_user,
            'nama_user'         => $this->createdUser->nama ?? null
        ];
    }
}
