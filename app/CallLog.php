<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CallLog extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'contact_id',
        'call_dial',
        'call_connect',
        'call_disconnect',
        'call_duration',
        'call_response'
    ];

    protected $dates = ['deleted_at'];
}
