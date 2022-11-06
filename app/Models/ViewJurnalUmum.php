<?php

namespace App\Models;

use App\Models\BaseModel;
use Illuminate\Notifications\Notifiable;

class ViewJurnalUmum extends BaseModel
{
    use Notifiable;

    protected $table = 'view_jurnal_umum_data';
}
