<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Layout extends Model
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'version',
        'products',
        'conversion',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'products' => 'array',
        'conversion' => 'array',
    ];
}
