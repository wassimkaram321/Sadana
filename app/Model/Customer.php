<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class Customer extends Authenticatable
{
    protected $table = 'users';

    protected $fillable = [
        'name',
        'f_name',
        'l_name',
        'phone',
        'image',
        'email',
        'user_type',
        'city',
        'street_address',
        'country',
        'pharmacy_id',
    ];

    use Notifiable, HasApiTokens;

}



