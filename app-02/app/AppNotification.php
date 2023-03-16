<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AppNotification extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
    	'notification_id',
		'name',
		'type',
		'message',
		'date',
		'tag_name',
		'user_id',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'date' => 'datetime',
    ];
}
