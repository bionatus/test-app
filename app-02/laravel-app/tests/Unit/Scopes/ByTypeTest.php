<?php

namespace Tests\Unit\Scopes;

use App\Models\PlainTag;
use App\Scopes\ByType;
use DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByTypeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     *
     * @param string $type
     * @param int    $expectedCount
     *
     * @dataProvider dataProvider
     */
    public function it_filters_the_result_by_type_column(string $type, int $expectedCount)
    {
        PlainTag::factory()->more()->count(2)->create();
        PlainTag::factory()->general()->count(3)->create();
        PlainTag::factory()->issue()->count(4)->create();

        $scope = new ByType($type);
        $query = DB::table(PlainTag::tableName());
        $scope->apply($query);

        $this->assertCount($expectedCount, $query->get());
    }

    public function dataProvider(): array
    {
        return [
            [PlainTag::TYPE_MORE, 2],
            [PlainTag::TYPE_GENERAL, 3],
            [PlainTag::TYPE_ISSUE, 4],
        ];
    }
}
