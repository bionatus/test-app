<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Malhal\Geographical\Geographical;

class Store extends Model
{
    use Geographical;

    const LATITUDE  = 'lat';
    const LONGITUDE = 'lng';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'address',
        'address2',
        'city',
        'state',
        'zip',
        'country',
        'country_iso',
        'phone',
        'fax',
        'email',
        'url',
        'image',
        'lat',
        'lng',
    ];
}
