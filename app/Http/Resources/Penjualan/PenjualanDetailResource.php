<?php
namespace App\Http\Resources\Penjualan;

use Illuminate\Http\Resources\Json\JsonResource;

class PenjualanDetailResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'            => $this->id,
            'id_penjualan'  => $this->id_penjualan,
            'id_barang'     => $this->id_barang,
            'kode_barang'   => $this->barang->kode_barang,
            'nama_barang'   => $this->barang->nama_barang,
            'satuan'        => $this->barang->satuan,
            'harga'         => $this->harga,
            'qty'           => $this->qty,
            'total'         => $this->total
        ];
    }
}
