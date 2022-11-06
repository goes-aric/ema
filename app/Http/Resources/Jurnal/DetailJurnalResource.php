<?php
namespace App\Http\Resources\Jurnal;

use Illuminate\Http\Resources\Json\JsonResource;

class DetailJurnalResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'        => $this->id,
            'no_jurnal' => $this->no_jurnal,
            'kode_akun' => $this->kode_akun,
            'nama_akun' => $this->nama_akun,
            'debet'     => $this->debet,
            'kredit'    => $this->kredit,
            'jurnal'    => $this->jurnal
        ];
    }
}
