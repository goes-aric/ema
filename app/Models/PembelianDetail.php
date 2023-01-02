<?php

namespace App\Models;

use App\Models\BaseModel;
use Illuminate\Notifications\Notifiable;

class PembelianDetail extends BaseModel
{
    use Notifiable;

    protected $searchable = [
        'columns' => [
            'pembelian_detail.id_barang' => 10,
            'pembelian_detail.harga' => 10,
            'pembelian_detail.qty' => 5,
            'pembelian_detail.total' => 5,
            'barang.kode_barang' => 10,
            'barang.nama_barang' => 10,
            'barang.satuan' => 5,
        ],
        'joins' => [
            'barang' => ['pembelian_detail.id_barang','barang.id'],
        ],
    ];

    protected $fillable = [
    	'id_pembelian', 'id_barang', 'harga', 'qty', 'total',
    ];

    protected $table = 'pembelian_detail';

    public function pembelian()
    {
        return $this->belongsTo('App\Models\Pembelian', 'id_pembelian');
    }

    public function barang()
    {
        return $this->belongsTo('App\Models\Barang', 'id_barang');
    }
}
