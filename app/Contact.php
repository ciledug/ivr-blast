<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
        'campaign_id',
        'account_id',
        'name',
        'phone',
        'bill_date',
        'due_date',
        'total_calls',
        'nominal',
        'extension',
        'callerid',
        'voice',
        'call_dial',
        'call_response',
        'created_at',
        'updated_at',
    ];
}
