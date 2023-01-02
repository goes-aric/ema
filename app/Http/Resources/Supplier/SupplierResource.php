<?php
namespace App\Http\Resources\Supplier;

use Illuminate\Http\Resources\Json\JsonResource;

class SupplierResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'            => $this->id,
            'nama_supplier' => $this->nama_supplier,
            'alamat'        => $this->alamat,
            'no_telp'       => $this->no_telp,
            'id_user'       => $this->createdUser->id,
            'nama_user'     => $this->createdUser->nama ?? null
        ];
    }
}
