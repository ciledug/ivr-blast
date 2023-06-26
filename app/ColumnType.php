<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ColumnType extends Model
{
    protected $fillable = [
        'name', 'type',
    ];
}
