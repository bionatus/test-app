<?php

namespace App\Models;

use App\Types\TaggableType;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Spatie\MediaLibrary\HasMedia;

interface IsTaggable extends HasMedia
{
    public function morphType(): string;

    public function getKey();

    public function getMorphClass();

    public function toTagType(bool $withMedia = false): TaggableType;

    public function taggableRouteKey(): string;

    public function tags(): MorphMany;

    public function posts(): MorphToMany;
}
