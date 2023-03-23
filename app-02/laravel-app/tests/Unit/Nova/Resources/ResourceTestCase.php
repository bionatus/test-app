<?php

namespace Tests\Unit\Nova\Resources;

use App\Nova\Resource;
use Epartment\NovaDependencyContainer\NovaDependencyContainer;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use JoshGaber\NovaUnit\Resources\InvalidNovaResourceException;
use JoshGaber\NovaUnit\Resources\NovaResourceTest;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Panel;
use Tests\TestCase;

abstract class ResourceTestCase extends TestCase
{
    use NovaResourceTest;

    protected function assertHasExpectedFields(string $resourceClass, array $fields)
    {
        /** @var Resource $resource */
        $resource = new $resourceClass(new $resourceClass::$model);

        $novaDependencyFieldNames = $this->getNovaDependencyFieldNames($resource->fields(new Request()));

        $fieldsWithoutNovaDependencyOnes = array_diff($fields,
            $novaDependencyFieldNames->pluck('attribute')->toArray());

        try {
            $mockResource = $this->novaResource($resourceClass);
            Collection::make($fieldsWithoutNovaDependencyOnes)->each(function(string $fieldName) use ($mockResource) {
                $mockResource->assertHasField($fieldName);
            });
        } catch (InvalidNovaResourceException $exception) {
            $this->fail('The supplied action class is not a Nova Resource.');
        }

        $resourceFields = $this->flattenFields($resource->fields(new Request()));

        $diff = array_diff($resourceFields->pluck('attribute')->toArray(), $fields);

        $this->assertCount(0, $diff, "Resource has fields not tested: " . implode(', ', $diff));
    }

    private function flattenFields(array $fields): Collection
    {
        return Collection::make($fields)->map(function($field) {
            if (is_a($field, Panel::class)) {
                return $this->flattenFields($field->data);
            }
            if (is_a($field, NovaDependencyContainer::class)) {
                return $this->flattenFields($field->meta()['fields']);
            }
            if (is_a($field, Field::class)) {
                return $field;
            }

            return false;
        })->flatten()->filter();
    }

    private function getNovaDependencyFieldNames(array $fields, bool $ignore = true): Collection
    {
        return Collection::make($fields)->map(function($field) use ($ignore) {
            if (is_a($field, Panel::class)) {
                return $this->getNovaDependencyFieldNames($field->data);
            }
            if (is_a($field, NovaDependencyContainer::class)) {
                return $this->getNovaDependencyFieldNames($field->meta()['fields'], false);
            }
            if (is_a($field, Field::class)) {
                return $ignore ? false : $field;
            }

            return false;
        })->flatten()->filter();
    }
}
