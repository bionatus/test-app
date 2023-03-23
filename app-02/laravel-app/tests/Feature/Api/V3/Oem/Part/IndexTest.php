<?php

namespace Tests\Feature\Api\V3\Oem\Part;

use App\Constants\RouteNames;
use App\Http\Controllers\Api\V3\Oem\PartController;
use App\Http\Resources\Api\V3\Oem\Part\BaseResource;
use App\Models\Oem;
use App\Models\OemPart;
use App\Models\Part;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see PartController */
class IndexTest extends TestCase
{
    use RefreshDatabase;

    private string $routeName = RouteNames::API_V3_OEM_PART_INDEX;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);
        $oem = Oem::factory()->create();

        $this->get(URL::route($this->routeName, $oem));
    }

    /** @test */
    public function it_displays_a_list_of_parts_sorted_by_functional_type_then_subcategory_and_part_type_coalesce_then_number(
    )
    {
        $oem = Oem::factory()->create();
        Part::factory()->other()->count(10)->create();
        Part::factory()->functional()->count(6)->create();
        Part::factory()->functional()->create(['subcategory' => 'AA Air Filter', 'number' => 'B']);
        Part::factory()->functional()->create(['subcategory' => 'AA Air Filter', 'number' => 'A']);
        Part::factory()->functional()->create(['subcategory' => 'AA Air Filter', 'number' => 'C']);

        $parts = Part::orderByRaw('type = "' . Part::TYPE_OTHER . '"')
            ->orderByRaw('coalesce(subcategory, type)')
            ->orderBy('number')
            ->get();
        $parts->each(fn(Part $part) => OemPart::factory()->usingOem($oem)->usingPart($part)->create());

        $route = URL::route($this->routeName, $oem);

        $this->login();
        $response = $this->get($route);
        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertCount($response->json('meta.total'), $parts);

        $data           = Collection::make($response->json('data'));
        $firstPageParts = $parts->values()->take(count($data));

        $data->each(function(array $rawPart, int $index) use ($firstPageParts) {
            $part = $firstPageParts->get($index);
            $this->assertSame($part->item->getRouteKey(), $rawPart['id']);
        });
    }
}
