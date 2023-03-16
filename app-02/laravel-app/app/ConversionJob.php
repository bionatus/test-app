<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ConversionJob extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'control',
        'standard',
        'optional',
        'image',
        'retrofit',
    ];
}
