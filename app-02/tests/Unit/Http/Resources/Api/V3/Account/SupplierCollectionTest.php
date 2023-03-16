<?php

namespace Tests\Unit\Http\Resources\Api\V3\Account;

use App\Http\Resources\Api\V3\Account\SupplierCollection;
use App\Http\Resources\Api\V3\Supplier\BaseResource;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierCollectionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_fields()
    {
        Supplier::factory()->count(20)->createQuietly();
        $page = Supplier::paginate();

        $resource = new SupplierCollection($page);
        $response = $resource->resolve();

        $data = [
            'data'          => BaseResource::collection($page),
            'next_page_url' => $page->nextPageUrl(),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(SupplierCollection::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
