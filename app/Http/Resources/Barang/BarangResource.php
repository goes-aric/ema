<?php
namespace App\Http\Resources\Barang;

use Illuminate\Http\Resources\Json\JsonResource;

class BarangResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'                => $this->id,
            'kode_barang'       => $this->kode_barang,
            'nama_barang'       => $this->nama_barang,
            'satuan'            => $this->satuan,
            'harga_beli'        => $this->harga_beli,
            'harga_jual'        => $this->harga_jual,
            'status'            => $this->status,
            'status_text'       => $this->status ? 'AKTIF' : 'DISCONTINUE',
            'status_digunakan'  => $this->status_digunakan,
            'id_user'           => $this->createdUser->id,
            'nama_user'         => $this->createdUser->nama ?? null
        ];
    }
}
