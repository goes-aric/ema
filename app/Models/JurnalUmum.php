<?php

namespace App\Models;

use App\Models\BaseModel;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class JurnalUmum extends BaseModel
{
    use Notifiable, SoftDeletes;

    protected $searchable = [
        'columns' => [
            'no_jurnal' => 10,
            'tanggal_transaksi' => 10,
            'deskripsi' => 5,
            'sumber' => 5,
            'kode_user' => 5,
        ]
    ];

    protected $dates = ['deleted_at'];

    protected $fillable = [
    	'no_jurnal', 'tanggal_transaksi', 'deskripsi', 'sumber', 'kode_user',
    ];

    protected $table = 'jurnal_umum';

    public function details()
    {
        return $this->hasMany('App\Models\DetailJurnalUmum', 'no_jurnal', 'no_jurnal');
    }
}
