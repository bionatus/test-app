<?php

namespace App\Traits;

use App\Models\Flag;
use App\Models\Scopes\ByName;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/** @mixin Model */
trait HasFlags
{
    public function flags(): MorphMany
    {
        return $this->morphMany(Flag::class, 'flaggable');
    }

    public function hasFlag(string $name): bool
    {
        return $this->flags()->scoped(new ByName($name))->exists();
    }

    public function flag($name): self
    {
        $this->flags()->firstOrCreate(['name' => $name]);

        return $this;
    }

    public function unflag(string $name): self
    {
        $this->flags()->scoped(new ByName($name))->delete();

        return $this;
    }
}
