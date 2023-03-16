<?php

namespace App\Models;

use App\Constants\Filesystem;
use Database\Factories\TechnicianFactory;
use Storage;

/**
 * @method static TechnicianFactory factory()
 *
 * @mixin Technician
 */
class Technician extends Model
{
    /* |--- GLOBAL VARIABLES ---| */

    protected $casts = [
        'id'                  => 'integer',
        'name'                => 'string',
        'code'                => 'string',
        'phone'               => 'string',
        'years_of_experience' => 'integer',
    ];

    /* |--- FUNCTIONS ---| */

    public function imageUrl(): ?string
    {
        return !empty($this->image) ? asset(Storage::disk(Filesystem::DISK_MEDIA)->url($this->image)) : null;
    }

    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
