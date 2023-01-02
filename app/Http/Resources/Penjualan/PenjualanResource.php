<?php
namespace App\Http\Resources\Penjualan;

use Illuminate\Http\Resources\Json\JsonResource;

class PenjualanResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'            => $this->id,
            'no_transaksi'  => $this->no_transaksi,
            'tanggal'       => $this->tanggal,
            'total'         => $this->total,
            'diskon'        => $this->diskon,
            'grand_total'   => $this->grand_total,
            'catatan'       => $this->catatan,
            'gambar'        => $this->gambar ? asset('/storage/images') . '/' . $this->gambar : null,
            'id_user'       => $this->createdUser->id,
            'nama_user'     => $this->createdUser->nama ?? null,
            'details'       => PenjualanDetailResource::collection($this->details)
        ];
    }
}
