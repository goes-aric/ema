<?php

namespace App\Models;

use App\Models\BaseModel;
use Illuminate\Notifications\Notifiable;
// use Illuminate\Database\Eloquent\SoftDeletes;

class Pembelian extends BaseModel
{
    // use Notifiable, SoftDeletes;
    use Notifiable;

    protected $searchable = [
        'columns' => [
            'pembelian.no_transaksi' => 10,
            'pembelian.tanggal' => 10,
            'pembelian.metode_bayar' => 5,
            'pembelian.id_supplier' => 5,
            'pembelian.total' => 5,
            'pembelian.diskon' => 5,
            'pembelian.grand_total' => 5,
            'pembelian.catatan' => 5,
            'pembelian.gambar' => 5,
            'pembelian.id_user' => 5,
            'supplier.nama_supplier' => 5,
        ],
        'joins' => [
            'supplier' => ['pembelian.id_supplier','supplier.id'],
        ],
    ];

    protected $dates = ['deleted_at'];

    protected $fillable = [
    	'no_transaksi', 'tanggal', 'metode_bayar', 'id_supplier', 'total', 'diskon', 'grand_total', 'catatan', 'gambar', 'id_user',
    ];

    protected $table = 'pembelian';

    public function supplier()
    {
        return $this->belongsTo('App\Models\Supplier', 'id_supplier');
    }

    public function details()
    {
        return $this->hasMany('App\Models\PembelianDetail', 'id_pembelian');
    }
}
