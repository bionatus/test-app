<?php

namespace App\Rules;

use App\Models\Tag;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Collection;

class SingleTagTypeMore implements Rule
{
    public function passes($attribute, $value): bool
    {
        $moreTags = $this->getMoreTags($value);

        return 1 >= $moreTags->count();
    }

    private function getMoreTags(array $tags): Collection
    {
        $tagsCollection = Collection::make($tags);

        return $tagsCollection->filter(function ($element) {
            return $this->isValidElement($element) && Tag::TYPE_MORE === $element['type'];
        });
    }

    private function isValidElement($element): bool
    {
        return is_array($element) && array_key_exists('type', $element) && array_key_exists('id', $element);
    }

    public function message(): string
    {
        return 'Only one tag of type more is allowed.';
    }
}
