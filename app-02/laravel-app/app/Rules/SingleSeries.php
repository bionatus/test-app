<?php

namespace App\Rules;

use App\Models\Tag;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Collection;

class SingleSeries implements Rule
{
    public function passes($attribute, $value): bool
    {
        $seriesTags = $this->getSeriesTags($value);

        return 1 >= $seriesTags->count();
    }

    private function getSeriesTags(array $tags): Collection
    {
        $tagsCollection = Collection::make($tags);

        return $tagsCollection->filter(function ($element) {
            return $this->isValidElement($element) && Tag::TYPE_SERIES === $element['type'];
        });
    }

    private function isValidElement($element): bool
    {
        return is_array($element) && array_key_exists('type', $element) && array_key_exists('id', $element);
    }

    public function message(): string
    {
        return 'Only one series is allowed.';
    }
}
