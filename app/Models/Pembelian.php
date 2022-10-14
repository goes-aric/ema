<?php

namespace App\Models;

use App\Models\BaseModel;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pembelian extends BaseModel
{
    use Notifiable, SoftDeletes;

    protected $searchable = [
        'columns' => [
            'kode_beli' => 10,
            'tanggal' => 10,
            'nominal' => 5,
            'metode_bayar' => 5,
            'uraian' => 5,
            'kode_akun_persediaan' => 5,
            'kode_akun_pembayaran' => 5,
            'kode_user' => 5,
        ]
    ];

    protected $dates = ['deleted_at'];

    protected $fillable = [
    	'kode_beli', 'tanggal', 'nominal', 'metode_bayar', 'uraian', 'kode_akun_persediaan', 'kode_akun_pembayaran', 'kode_user',
    ];

    protected $table = 'pembelian';

    public function akunPersediaan()
    {
        return $this->belongsTo('App\Models\Akun', 'kode_akun_persediaan', 'kode_akun');
    }

    public function akunPembayaran()
    {
        return $this->belongsTo('App\Models\Akun', 'kode_akun_pembayaran', 'kode_akun');
    }
}
