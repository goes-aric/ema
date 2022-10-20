<?php

namespace App\Models;

use App\Models\BaseModel;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Akun extends BaseModel
{
    use Notifiable, SoftDeletes;

    protected $searchable = [
        'columns' => [
            'kode_akun' => 10,
            'nama_akun' => 10,
            'akun_utama' => 5,
            'tipe_akun' => 5,
        ]
    ];

    protected $dates = ['deleted_at'];

    protected $fillable = [
    	'kode_akun', 'nama_akun', 'akun_utama', 'tipe_akun', 'kode_user',
    ];

    protected $table = 'akun';

    public function akunInduk()
    {
        return $this->belongsTo('App\Models\Akun', 'akun_utama', 'kode_akun');
    }
}
