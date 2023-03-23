<?php

namespace Tests\Unit\Nova\Resources;

use App\Models\SupportCallCategory;
use App\Nova\Resources;
use DB;
use Laravel\Nova\Http\Requests\NovaRequest;
use Mockery;
use Str;

class SupportCallCategoryTest extends ResourceTestCase
{
    /** @test */
    public function it_uses_correct_model()
    {
        $this->assertSame(SupportCallCategory::class, Resources\SupportCallCategory::$model);
    }

    /** @test */
    public function it_uses_the_name_as_title()
    {
        $this->assertSame('name', Resources\SupportCallCategory::$title);
    }

    /** @test */
    public function it_uses_fields_for_search()
    {
        $this->assertSame([
            'id',
            'name',
        ], Resources\SupportCallCategory::$search);
    }

    /** @test */
    public function it_should_be_displayed_in_navigation()
    {
        $this->assertTrue(Resources\SupportCallCategory::$displayInNavigation);
    }

    /** @test */
    public function it_uses_current_as_group()
    {
        $this->assertEquals('Current', Resources\SupportCallCategory::group());
    }

    /** @test */
    public function it_has_expected_fields()
    {
        $this->assertHasExpectedFields(Resources\SupportCallCategory::class, [
            'id',
            'name',
            'description',
            'phone',
            'sort',
            'parent',
            'images',
            'children',
            'instruments',
        ]);
    }

    /** @test
     * @dataProvider requestDataProvider
     */
    public function it_has_a_custom_relatable_query($expected, $relatableModel, $isUpdateOrUpdateAttachedRequest)
    {
        $request = Mockery::mock(NovaRequest::class);
        $request->shouldReceive('isUpdateOrUpdateAttachedRequest')
            ->withNoArgs()
            ->andReturn($isUpdateOrUpdateAttachedRequest);
        $request->shouldReceive('get')->with('resourceId')->andReturn($resourceId = 55);

        $builder = SupportCallCategory::query();
        $query   = Resources\SupportCallCategory::relatableQuery($request, $builder);

        if (DB::getDefaultConnection() == 'sqlite') {
            $expected = Str::replace('`', '"', $expected);
        }

        $this->assertSame(Str::lower($expected), Str::lower($query->toSql()));

        if ($isUpdateOrUpdateAttachedRequest) {
            $this->assertSame($query->getBindings()[0], $resourceId);
        }
    }

    /** @noinspection SqlResolve */
    public function requestDataProvider(): array
    {
        $supportCallCategoriesTableName = SupportCallCategory::tableName();

        $queryForCreate = "SELECT * FROM `{$supportCallCategoriesTableName}` WHERE `parent_id` IS NULL";
        $queryForUpdate = "SELECT * FROM `{$supportCallCategoriesTableName}` WHERE `parent_id` IS NULL AND `id` <> ?";

        return [
            [$queryForCreate, Resources\SupportCallCategory::uriKey(), false],
            [$queryForUpdate, Resources\SupportCallCategory::uriKey(), true],
        ];
    }
}
