<?php

namespace Tests\Feature\Api\V2\Taggable;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Http\Controllers\Api\V2\TaggableController;
use App\Http\Resources\Api\V2\Tag\ImagedResource;
use App\Models\Brand;
use App\Models\IsTaggable;
use App\Models\Media;
use App\Models\Model;
use App\Models\ModelType;
use App\Models\Oem;
use App\Models\PlainTag;
use App\Models\Series;
use App\Models\Tag;
use Config;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\Feature\Api\V2\WithLatamMiddlewares;
use Tests\TestCase;
use URL;

/** @see TaggableController */
class IndexTest extends TestCase
{
    use RefreshDatabase;
    use WithLatamMiddlewares;

    private string $routeName = RouteNames::API_V2_TAGGABLE_INDEX;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->get(URL::route($this->routeName));
    }

    /** @test */
    public function it_display_a_list_of_tags()
    {
        PlainTag::factory()->more()->count(2)->create();
        PlainTag::factory()->general()->count(2)->create();
        PlainTag::factory()->issue()->count(2)->create();
        $plainTags = PlainTag::all();

        ModelType::factory()->create();
        $seriesTags    = Series::factory()->count(2)->create();
        $modelTypeTags = ModelType::all();

        $totalTagsCount = $plainTags->count() + $seriesTags->count() + $modelTypeTags->count();

        $this->login();
        $response = $this->get(URL::route($this->routeName));

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(ImagedResource::jsonSchema(), true), $response);
        $this->assertSame($totalTagsCount, $response->json('meta.total'));

        $data = Collection::make($response->json('data'));

        $tags = Collection::make()->merge($plainTags)->merge($seriesTags)->merge($modelTypeTags);

        $tags->each(function(IsTaggable $taggable) use ($data) {
            /** @var IsTaggable|Model $taggable */
            $elementInData = $data->where('type', $taggable->morphType())
                ->where('id', $taggable->getRouteKey())
                ->first();
            $this->assertNotNull($elementInData);
        });
    }

    /** @test */
    public function the_list_of_tags_is_paginated_defaults_page_size_to_configured_per_page()
    {
        $this->login();
        $response = $this->get(URL::route($this->routeName));

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(ImagedResource::jsonSchema(), true), $response);
        $this->assertSame(Config::get('pagination.per_page'), $response->json('meta.per_page'));
    }

    /** @test */
    public function page_size_can_be_specified()
    {
        $this->login();

        $parameters = [RequestKeys::PER_PAGE => 10];

        $response = $this->get(URL::route($this->routeName, $parameters));
        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(ImagedResource::jsonSchema(), true), $response);
        $this->assertSame(10, $response->json('meta.per_page'));
    }

    /** @test */
    public function page_size_zero_brings_all_records()
    {
        PlainTag::factory()->count(50)->create();

        $this->login();

        $parameters = [RequestKeys::PER_PAGE => 0];

        $response = $this->get(URL::route($this->routeName, $parameters));

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(ImagedResource::jsonSchema(), true), $response);
        $this->assertSame(50, $response->json('meta.per_page'));
    }

    /** @test */
    public function page_size_zero_with_no_records_defaults_to_configured_per_page()
    {
        $this->login();

        $parameters = [RequestKeys::PER_PAGE => 0];

        $response = $this->get(URL::route($this->routeName, $parameters));

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(ImagedResource::jsonSchema(), true), $response);
        $this->assertSame(Config::get('pagination.per_page'), $response->json('meta.per_page'));
    }

    /** @test */
    public function invalid_page_size_defaults_to_configured_per_page()
    {
        $this->login();

        $parameters = [RequestKeys::PER_PAGE => 'invalid'];

        $response = $this->get(URL::route($this->routeName, $parameters));

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(ImagedResource::jsonSchema(), true), $response);
        $this->assertSame(Config::get('pagination.per_page'), $response->json('meta.per_page'));
    }

    /** @test */
    public function validated_parameters_are_present_in_pagination_links()
    {
        $this->login();

        $parameters = [RequestKeys::PER_PAGE => 10, 'invalid' => 'invalid', RequestKeys::TYPE => Tag::TYPE_GENERAL];

        $response = $this->get(URL::route($this->routeName, $parameters));
        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(ImagedResource::jsonSchema(), true), $response);

        $parsedUrl = parse_url($response->json('links.first'));
        parse_str($parsedUrl['query'], $queryString);

        $this->assertArrayHasKey(RequestKeys::PER_PAGE, $queryString);
        $this->assertArrayHasKey(RequestKeys::TYPE, $queryString);
        $this->assertArrayNotHasKey('invalid', $queryString);
    }

    /** @test */
    public function the_list_of_tags_are_sorted_by_name_alphabetically()
    {
        $plainTags     = PlainTag::factory()->count(50)->create();
        $seriesTags    = Series::factory()->count(25)->create();
        $modelTypeTags = ModelType::all();

        $tags = Collection::make()->merge($plainTags)->merge($seriesTags)->merge($modelTypeTags);

        $this->login();
        $response = $this->get(URL::route($this->routeName));
        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(ImagedResource::jsonSchema(), true), $response);

        $data          = Collection::make($response->json('data'));
        $firstPageTags = $tags->sortBy(function($item, $key) {
            if ($item->morphType() === 'series') {
                $brandName = $item->brand ? $item->brand->name : '';

                return $brandName . '|' . $item->name;
            }

            return $item->name;
        })->values()->take(count($data));

        $data->each(function(array $tag) use ($firstPageTags) {
            $element = $firstPageTags->first(function($item) use ($tag) {
                return ($item->getRouteKey() == $tag['id']) && ($item->morphType() === $tag['type']);
            });
            $this->assertNotNull($element);
            $this->assertEquals($element->getRouteKey(), $tag['id']);
        });
    }

    /** @test
     * @throws Exception
     */
    public function it_returns_the_image_of_the_tags()
    {
        PlainTag::factory()->more()->count(2)->create();
        PlainTag::factory()->general()->count(2)->create();
        PlainTag::factory()->issue()->count(2)->create();
        ModelType::factory()->create();

        $plainTagWithImages = PlainTag::factory()->issue()->create();
        $plainTagMedia      = Media::factory()->usingTag($plainTagWithImages)->create();

        $modelTypeWithImages = ModelType::factory()->create();
        $modelTypeMedia      = Media::factory()->usingTag($modelTypeWithImages)->create();

        $seriesWithImages = Series::factory()->create();

        Series::factory()->count(2)->create();

        $this->login();
        $response = $this->get(URL::route($this->routeName));

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(ImagedResource::jsonSchema(), true), $response);

        $data = Collection::make($response->json('data'));

        $tagTypeWithImages    = $plainTagWithImages->toTagType();
        $responseTagWithImage = $data->first(function($element) use ($tagTypeWithImages) {
            return $tagTypeWithImages->type === $element['type'] && $tagTypeWithImages->id === $element['id'];
        });

        $this->assertNotNull($responseTagWithImage);
        $this->assertNotEmpty($responseTagWithImage['images']['data']);
        $this->assertEquals($plainTagMedia->uuid, $responseTagWithImage['images']['data'][0]['id']);

        $seriesTypeWithImages    = $seriesWithImages->toTagType();
        $responseSeriesWithImage = $data->first(function($element) use ($seriesTypeWithImages) {
            return $seriesTypeWithImages->type === $element['type'] && $seriesTypeWithImages->id === $element['id'];
        });

        $this->assertNotNull($responseSeriesWithImage);
        $this->assertNotEmpty($responseSeriesWithImage['images']['data']);
        $this->assertEquals($seriesWithImages->image, $responseSeriesWithImage['images']['data'][0]['url']);

        $modelTypeTageTypeWithImages = $modelTypeWithImages->toTagType();
        $responseModelTypeWithImages = $data->first(function($element) use ($modelTypeTageTypeWithImages) {
            return $modelTypeTageTypeWithImages->type === $element['type'] && $modelTypeTageTypeWithImages->id === $element['id'];
        });

        $this->assertNotNull($responseModelTypeWithImages);
        $this->assertNotEmpty($responseModelTypeWithImages['images']['data']);
        $this->assertEquals($modelTypeMedia->uuid, $responseModelTypeWithImages['images']['data'][0]['id']);
    }

    /**
     * @test
     *
     * @param string $type
     * @param int    $expectedCount
     *
     * @dataProvider typeFilterDataProvider
     */
    public function the_tags_can_be_filtered_by_type(string $type, int $expectedCount)
    {
        $this->withoutExceptionHandling();
        PlainTag::factory()->more()->count(2)->create();
        PlainTag::factory()->general()->count(3)->create();
        PlainTag::factory()->issue()->count(4)->create();
        ModelType::factory()->count(5)->create();
        Series::factory()->count(10)->create();

        $this->login();

        $parameters = [RequestKeys::TYPE => $type];

        $response = $this->get(URL::route($this->routeName, $parameters));

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(ImagedResource::jsonSchema(), true), $response);

        $data = Collection::make($response->json('data'));

        $this->assertCount($expectedCount, $data);
    }

    public function typeFilterDataProvider(): array
    {
        return [
            [Tag::TYPE_MORE, 2],
            [Tag::TYPE_GENERAL, 3],
            [Tag::TYPE_ISSUE, 4],
            [Tag::TYPE_SERIES, 10],
            [Tag::TYPE_MODEL_TYPE, 5],
        ];
    }

    /** @test */
    public function series_tags_can_be_filtered_by_brand_id()
    {
        PlainTag::factory()->count(2)->create();
        $brands = Brand::factory()->count(3)->create();
        foreach ($brands as $brand) {
            Series::factory()->count(2)->usingBrand($brand)->create();
        }

        $filteredBrand = Brand::factory()->create();
        Series::factory()->usingBrand($filteredBrand)->count(5)->create();

        $this->login();

        $parameters = [RequestKeys::TYPE => Tag::TYPE_SERIES, RequestKeys::BRAND => $filteredBrand->getRouteKey()];

        $response = $this->get(URL::route($this->routeName, $parameters));

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(ImagedResource::jsonSchema(), true), $response);

        $data = Collection::make($response->json('data'));

        $this->assertCount(5, $data);
    }

    /** @test */
    public function model_type_tags_can_be_filtered_by_series_id()
    {
        PlainTag::factory()->count(2)->create();
        Series::factory()->count(2)->create();
        ModelType::factory()->count(2)->create();
        $series    = Series::factory()->create();
        $modelType = ModelType::factory()->create();
        Oem::factory()->usingModelType($modelType)->usingSeries($series)->create();

        $this->login();

        $parameters = [RequestKeys::TYPE => Tag::TYPE_MODEL_TYPE, RequestKeys::SERIES => $series->getRouteKey()];

        $response = $this->get(URL::route($this->routeName, $parameters));

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(ImagedResource::jsonSchema(), true), $response);

        $data = Collection::make($response->json('data'));

        $this->assertCount(1, $data);
        $this->assertEquals($data->first()['id'], $modelType->getRouteKey());
    }
}
