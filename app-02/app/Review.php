<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'summary',
        'review',
        'video_url',
        'image',
        'price',
        'sale_price',
        'value',
        'utility',
        'score',
        'url_label',
        'url',
    ];
}
