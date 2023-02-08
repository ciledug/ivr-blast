<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserLog extends Model
{
    protected $fillable = [
        'user_id', 'last_login', 'last_ip_address',
    ];

    protected $dates = ['deleted_at'];

}
