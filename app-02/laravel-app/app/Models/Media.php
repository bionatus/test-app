<?php

namespace App\Models;

use Database\Factories\MediaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\MediaCollections\Models\Media as BaseMedia;

/**
 * @method static MediaFactory factory()
 * @method static static create($attributes = [])
 *
 * @mixin Media
 */
class Media extends BaseMedia
{
    use HasFactory;
}
