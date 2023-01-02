<?php

namespace App\Models;

use App\Models\BaseModel;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends BaseModel
{
    use Notifiable, SoftDeletes;

    protected $searchable = [
        'columns' => [
            'nama_supplier' => 10,
            'alamat' => 10,
            'no_telp' => 5,
        ]
    ];

    protected $dates = ['deleted_at'];

    protected $fillable = [
    	'nama_supplier', 'alamat', 'no_telp', 'id_user',
    ];

    protected $table = 'supplier';
}
