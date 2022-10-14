<?php

namespace App\Models;

use App\Models\BaseModel;
use Illuminate\Notifications\Notifiable;

class DetailJurnalUmum extends BaseModel
{
    use Notifiable;

    protected $searchable = [
        'columns' => [
            'no_jurnal' => 10,
            'kode_akun' => 10,
            'nama_akun' => 5,
            'debet' => 5,
            'kredit' => 5,
        ]
    ];

    protected $dates = ['deleted_at'];

    protected $fillable = [
    	'no_jurnal', 'kode_akun', 'nama_akun', 'debet', 'kredit',
    ];

    protected $table = 'detail_jurnal_umum';
}
