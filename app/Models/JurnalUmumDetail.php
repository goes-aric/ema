<?php

namespace App\Models;

use App\Models\BaseModel;
use Illuminate\Notifications\Notifiable;

class JurnalUmumDetail extends BaseModel
{
    use Notifiable;

    protected $searchable = [
        'columns' => [
            'id_jurnal_umum' => 10,
            'kode_akun' => 10,
            'nama_akun' => 5,
            'debet' => 5,
            'kredit' => 5,
        ]
    ];

    protected $dates = ['deleted_at'];

    protected $fillable = [
    	'id_jurnal_umum', 'kode_akun', 'nama_akun', 'debet', 'kredit',
    ];

    protected $table = 'jurnal_umum_detail';

    public function jurnal()
    {
        return $this->belongsTo('App\Models\JurnalUmum', 'id_jurnal_umum');
    }
}
