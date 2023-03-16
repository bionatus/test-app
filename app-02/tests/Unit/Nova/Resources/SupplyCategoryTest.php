<?php

namespace Tests\Unit\Nova\Resources;

use App\Models\Supply;
use App\Models\SupplyCategory;
use App\Nova\Resources;
use DB;
use Laravel\Nova\Http\Requests\NovaRequest;
use Mockery;
use Str;

class SupplyCategoryTest extends ResourceTestCase
{
    /** @test */
    public function it_uses_correct_model()
    {
        $this->assertSame(SupplyCategory::class, Resources\SupplyCategory::$model);
    }

    /** @test */
    public function it_uses_the_name_as_title()
    {
        $this->assertSame('name', Resources\SupplyCategory::$title);
    }

    /** @test */
    public function it_uses_fields_for_search()
    {
        $this->assertSame([
            'id',
            'name',
        ], Resources\SupplyCategory::$search);
    }

    /** @test */
    public function it_should_be_displayed_in_navigation()
    {
        $this->assertTrue(Resources\SupplyCategory::$displayInNavigation);
    }

    /** @test */
    public function it_uses_current_as_group()
    {
        $this->assertEquals('Current', Resources\SupplyCategory::group());
    }

    /** @test */
    public function it_has_expected_fields()
    {
        $this->assertHasExpectedFields(Resources\SupplyCategory::class, [
            'id',
            'name',
            'sort',
            'visible_at',
            'parent',
            'ComputedField',
            'ComputedField',
            'images',
            'supplies',
            'children',
        ]);
    }

    /**
     * @test
     * @dataProvider requestDataProvider
     */
    public function it_has_a_custom_relatable_query($expected, $relatableModel, $isUpdateOrUpdateAttachedRequest)
    {
        $request = Mockery::mock(NovaRequest::class);
        $request->shouldReceive('isUpdateOrUpdateAttachedRequest')
            ->withNoArgs()
            ->andReturn($isUpdateOrUpdateAttachedRequest);
        $request->shouldReceive('get')->with('resourceId')->andReturn($resourceId = 55);
        $request->shouldReceive('getPathInfo')
            ->withNoArgs()
            ->once()
            ->andReturn(DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR . $relatableModel);

        $builder  = SupplyCategory::query();
        $query    = Resources\SupplyCategory::relatableQuery($request, $builder);
        $expected = Str::replace('`', '"', Str::lower($expected));
        $actual      = Str::replace('`', '"', Str::lower($query->toSql()));

        if (Str::contains($expected, $reserved = ':reserved')) {
            $reserved1 = Str::between($actual, ' as "', '" where ');
            $reserved2 = Str::between($actual, '."id" = "', '"."parent_id"');
            $expected  = Str::replaceArray($reserved, [$reserved1, $reserved2], $expected);
        }

        $this->assertSame($expected, $actual);

        if ($isUpdateOrUpdateAttachedRequest) {
            $this->assertSame($query->getBindings()[0], $resourceId);
        }
    }

    /** @noinspection SqlResolve */
    public function requestDataProvider(): array
    {
        $supplyCategoriesTableName = SupplyCategory::tableName();
        $suppliesTableName         = Supply::tableName();

        $queryForCreate = "SELECT * FROM `{$supplyCategoriesTableName}` WHERE NOT EXISTS (SELECT * FROM `{$suppliesTableName}` WHERE `{$supplyCategoriesTableName}`.`id` = `{$suppliesTableName}`.`supply_category_id`)";
        $queryForUpdate = "SELECT * FROM `{$supplyCategoriesTableName}` WHERE NOT EXISTS (SELECT * FROM `{$suppliesTableName}` WHERE `{$supplyCategoriesTableName}`.`id` = `{$suppliesTableName}`.`supply_category_id`) AND `id` <> ?";
        $queryForNull   = "SELECT * FROM `{$supplyCategoriesTableName}` WHERE NOT EXISTS (SELECT * FROM `{$supplyCategoriesTableName}` AS `:reserved` WHERE `{$supplyCategoriesTableName}`.`id` = `:reserved`.`parent_id`)";

        return [
            [$queryForCreate, Resources\SupplyCategory::uriKey(), false],
            [$queryForUpdate, Resources\SupplyCategory::uriKey(), true],
            [$queryForNull, Resources\Supply::uriKey(), null],
        ];
    }
}
