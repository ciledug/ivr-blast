<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'campaign_id',
        'account_id',
        'name',
        'phone',
        'bill_date',
        'due_date',
        'nominal',
        'call_dial',
        'call_connect',
        'call_disconnect',
        'call_duration',
        'call_response',
    ];

    protected $dates = ['deleted_at'];
}
