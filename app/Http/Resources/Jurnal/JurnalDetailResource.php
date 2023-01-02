<?php
namespace App\Http\Resources\Jurnal;

use Illuminate\Http\Resources\Json\JsonResource;

class JurnalDetailResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'        => $this->id,
            'id_jurnal' => $this->id_jurnal,
            'id_akun'   => $this->id_akun,
            'kode_akun' => $this->kode_akun,
            'nama_akun' => $this->nama_akun,
            'debet'     => $this->debet,
            'kredit'    => $this->kredit,
            'jurnal'    => $this->jurnal
        ];
    }
}
