<?php

namespace App\Models;

use App\Models\BaseModel;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Barang extends BaseModel
{
    use Notifiable, SoftDeletes;

    protected $searchable = [
        'columns' => [
            'kode_barang' => 10,
            'nama_barang' => 10,
            'satuan' => 5,
            'harga_beli' => 5,
            'harga_jual' => 5,
            'status' => 5
        ]
    ];

    protected $dates = ['deleted_at'];

    protected $fillable = [
    	'kode_barang', 'nama_barang', 'satuan', 'harga_beli', 'harga_jual', 'status', 'status_digunakan', 'id_user',
    ];

    protected $table = 'barang';
}
