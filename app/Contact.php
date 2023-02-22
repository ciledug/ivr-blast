<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $fillable = [
        'campaign_id',
        'account_id',
        'name',
        'phone',
        'bill_date',
        'due_date',
        'nominal',
        'extension',
        'callerid',
        'call_dial',
        'call_response',
    ];
}
