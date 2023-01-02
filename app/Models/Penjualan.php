<?php

namespace App\Models;

use App\Models\BaseModel;
use Illuminate\Notifications\Notifiable;
// use Illuminate\Database\Eloquent\SoftDeletes;

class Penjualan extends BaseModel
{
    // use Notifiable, SoftDeletes;
    use Notifiable;

    protected $searchable = [
        'columns' => [
            'no_transaksi' => 10,
            'tanggal' => 10,
            'total' => 5,
            'diskon' => 5,
            'grand_total' => 5,
            'catatan' => 5,
            'gambar' => 5,
            'id_user' => 5,
        ]
    ];

    protected $dates = ['deleted_at'];

    protected $fillable = [
    	'no_transaksi', 'tanggal', 'total', 'diskon', 'grand_total', 'catatan', 'gambar', 'id_user',
    ];

    protected $table = 'penjualan';

    public function details()
    {
        return $this->hasMany('App\Models\PenjualanDetail', 'id_penjualan');
    }
}
