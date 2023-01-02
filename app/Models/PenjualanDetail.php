<?php

namespace App\Models;

use App\Models\BaseModel;
use Illuminate\Notifications\Notifiable;

class PenjualanDetail extends BaseModel
{
    use Notifiable;

    protected $searchable = [
        'columns' => [
            'penjualan_detail.id_barang' => 10,
            'penjualan_detail.harga' => 10,
            'penjualan_detail.qty' => 5,
            'penjualan_detail.total' => 5,
            'barang.kode_barang' => 10,
            'barang.nama_barang' => 10,
            'barang.satuan' => 5,
        ],
        'joins' => [
            'barang' => ['penjualan_detail.id_barang','barang.id'],
        ],
    ];

    protected $fillable = [
    	'id_penjualan', 'id_barang', 'harga', 'qty', 'total',
    ];

    protected $table = 'penjualan_detail';

    public function penjualan()
    {
        return $this->belongsTo('App\Models\Penjualan', 'id_penjualan');
    }

    public function barang()
    {
        return $this->belongsTo('App\Models\Barang', 'id_barang');
    }
}
