<?php

namespace App\Models;

use App\Models\BaseModel;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Penjualan extends BaseModel
{
    use Notifiable, SoftDeletes;

    protected $searchable = [
        'columns' => [
            'kode_jual' => 10,
            'tanggal' => 10,
            'nominal' => 5,
            'uraian' => 5,
            'kode_akun_persediaan' => 5,
            'kode_akun_penerimaan' => 5,
            'kode_user' => 5,
        ]
    ];

    protected $dates = ['deleted_at'];

    protected $fillable = [
    	'kode_jual', 'tanggal', 'nominal', 'uraian', 'kode_akun_persediaan', 'kode_akun_penerimaan', 'kode_user',
    ];

    protected $table = 'penjualan';

    public function akunPersediaan()
    {
        return $this->belongsTo('App\Models\Akun', 'kode_akun_persediaan', 'kode_akun');
    }

    public function akunPenerimaan()
    {
        return $this->belongsTo('App\Models\Akun', 'kode_akun_penerimaan', 'kode_akun');
    }
}
