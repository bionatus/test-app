<?php

namespace App\Rules;

use App\Models\Tag;
use App\Types\TaggablesCollection;
use App\Types\TaggableType;
use Exception;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Collection;
use Validator;

class ExistingIncomingRawTag implements Rule
{
    private string              $message;
    private TaggablesCollection $taggables;
    private Collection          $taggableTypes;

    public function __construct()
    {
        $this->taggables     = TaggablesCollection::make();
        $this->taggableTypes = Collection::make();
    }

    public function passes($attribute, $value): bool
    {
        if (!is_array($value)) {
            return $this->fail('Tag must be an array.');
        }

        if (!isset($value['type'])) {
            return $this->fail('Tag must have a "type" key.');
        }

        if (!isset($value['id'])) {
            return $this->fail('Tag must have an "id" key.');
        }

        $type = $value['type'];
        if (!is_string($type)) {
            return $this->fail('Tag Type must be a string.');
        }

        $id = $value['id'];
        if (!is_string($id) && !is_integer($id)) {
            return $this->fail('Tag ID must be a string or integer.');
        }

        $validator = Validator::make(["{$attribute}.type" => $type], [
            $attribute => [\Illuminate\Validation\Rule::in(array_keys(Tag::MORPH_MODEL_MAPS))],
        ]);

        if ($validator->fails()) {
            return $this->fail('Invalid Type in Tag.');
        }

        try {
            $taggableType = new TaggableType($value);

            $taggable = $taggableType->taggable();
            if(null === $taggable){
                throw new Exception();
            }
            $this->taggables->push($taggable);
            $this->taggableTypes->push($taggableType);

        } catch (Exception $exception) {
            $this->message = 'Invalid tag.';

            return false;
        }

        return true;
    }

    public function message(): string
    {
        return $this->message;
    }

    private function fail(string $message): bool
    {
        $this->message = $message;

        return false;
    }

    public function taggables(): TaggablesCollection
    {
        return $this->taggables;
    }

    public function taggableTypes(): Collection
    {
        return $this->taggableTypes;
    }
}
