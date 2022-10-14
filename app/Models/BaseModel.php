<?php
namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Nicolaslopezj\Searchable\SearchableTrait;
use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    use HasApiTokens, SearchableTrait, Notifiable;

    public function createdUser()
    {
        return $this->belongsTo('App\Models\User', 'kode_user', 'kode_user');
    }
}
