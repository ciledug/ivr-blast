<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Campaign extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'unique_key', 'name', 'total_data', 'status', 'created_by', 'template_id', 'reference_table',
        'text_voice', 'voice_gender',
    ];

    protected $dates = ['deleted_at'];
}
