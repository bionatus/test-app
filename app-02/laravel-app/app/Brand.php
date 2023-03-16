<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [ 'name' ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'logo' => 'array',
    ];

    /**
     * Get the seris for the Brand.
     */
    public function series() {
        return $this->hasMany(Series::class);
    }
}
