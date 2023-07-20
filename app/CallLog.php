<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CallLog extends Model
{
    
    protected $fillable = [
        'campaign_id',
        'contact_id',
        'call_dial',
        'call_connect',
        'call_disconnect',
        'call_duration',
        'call_recording',
        'call_response'
    ];
}
