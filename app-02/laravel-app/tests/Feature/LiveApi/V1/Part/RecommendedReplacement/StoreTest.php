<?php

namespace Tests\Feature\LiveApi\V1\Part\RecommendedReplacement;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Http\Controllers\LiveApi\V1\Part\RecommendedReplacementController;
use App\Http\Requests\LiveApi\V1\Part\RecommendedReplacement\StoreRequest;
use App\Http\Resources\LiveApi\V1\Part\RecommendedReplacement\BaseResource;
use App\Models\Part;
use App\Models\RecommendedReplacement;
use App\Models\Staff;
use App\Models\Supplier;
use Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JMac\Testing\Traits\AdditionalAssertions;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see RecommendedReplacementController */
class StoreTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = RouteNames::LIVE_API_V1_PART_RECOMMENDED_REPLACEMENT_STORE;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();
        $this->expectException(UnauthorizedHttpException::class);

        $part = Part::factory()->create();

        $this->post(URL::route($this->routeName, [RouteParameters::PART => $part->item->getRouteKey()]));
    }

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, StoreRequest::class);
    }

    /** @test */
    public function it_stores_a_recommended_replacement_for_the_part()
    {
        $supplier = Supplier::factory()->createQuietly();
        $staff    = Staff::factory()->usingSupplier($supplier)->create();
        $part     = Part::factory()->create();

        $route = URL::route($this->routeName, [RouteParameters::PART => $part->item->getRouteKey()]);

        Auth::shouldUse('live');
        $this->login($staff);
        $response = $this->post($route, [
            RequestKeys::BRAND       => $brand = 'brand name',
            RequestKeys::PART_NUMBER => $partNumber = 'a part number',
            RequestKeys::NOTE        => $note = 'a note',
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);

        $this->assertDatabaseHas(RecommendedReplacement::tableName(), [
            'supplier_id'      => $supplier->getKey(),
            'original_part_id' => $part->getKey(),
            'brand'            => $brand,
            'part_number'      => $partNumber,
            'note'             => $note,
        ]);
    }
}
