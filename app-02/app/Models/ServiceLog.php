<?php

namespace App\Models;

use Config;
use Database\Factories\ServiceLogFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @method static ServiceLogFactory factory()
 *
 * @mixin ServiceLog
 */
class ServiceLog extends Model
{
    /* |--- CONSTANTS ---| */
    const POLYMORPHIC_NAME = 'causer';
    /* |--- GLOBAL VARIABLES ---| */
    protected $casts = [
        'id'        => 'integer',
        'causer_id' => 'integer',
    ];

    /* |--- FUNCTIONS ---| */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->connection = Config::get('database.default_stats');
    }

    /* |--- RELATIONS ---| */
    public function causer(): MorphTo
    {
        $connection = Config::get('database.default');

        return $this->setConnection($connection)->morphTo();
    }
    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
