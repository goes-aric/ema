<?php
namespace App\Http\Resources\Pembelian;

use Illuminate\Http\Resources\Json\JsonResource;

class PembelianDetailResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'            => $this->id,
            'id_pembelian'  => $this->id_pembelian,
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
