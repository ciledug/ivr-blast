<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TemplateHeader extends Model
{
    protected $fillable = [
        'template_id',
        'name',
        'column_type',
        'is_mandatory',
        'is_unique',
        'is_voice',
        'voice_position',
    ];
}
