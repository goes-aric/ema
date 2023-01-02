<?php
namespace App\Http\Resources\Pembelian;

use Illuminate\Http\Resources\Json\JsonResource;

class PembelianResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'            => $this->id,
            'no_transaksi'  => $this->no_transaksi,
            'tanggal'       => $this->tanggal,
            'metode_bayar'  => $this->metode_bayar,
            'id_supplier'   => $this->id_supplier,
            'supplier'      => $this->supplier,
            'total'         => $this->total,
            'diskon'        => $this->diskon,
            'grand_total'   => $this->grand_total,
            'catatan'       => $this->catatan,
            'gambar'        => $this->gambar ? asset('/storage/images') . '/' . $this->gambar : null,
            'id_user'       => $this->createdUser->id,
            'nama_user'     => $this->createdUser->nama ?? null,
            'details'       => PembelianDetailResource::collection($this->details)
        ];
    }
}
