<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Series extends Model
{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'series';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'image',
        'brand_id',
    ];

    /**
     * Get the brand for the Series.
     */
    public function brand() {
        return $this->belongsTo(Brand::class);
    }

    /**
     * Get the products for the Series.
     */
    public function products() {
        return $this->hasMany(Product::class);
    }
}
