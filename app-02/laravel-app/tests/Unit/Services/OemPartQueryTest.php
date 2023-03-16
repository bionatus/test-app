<?php

namespace Tests\Unit\Services;

use App\Models\Oem;
use App\Models\OemDetailCounter;
use App\Models\Part;
use App\Models\PartDetailCounter;
use App\Models\User;
use App\Services\OemPartQuery;
use App\Types\RecentlyViewed;
use DB;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Tests\TestCase;

class OemPartQueryTest extends TestCase
{
    use RefreshDatabase;

    /** @test
     * @throws Exception
     */
    public function it_returns_a_query_of_oems_and_parts_by_user_ordered_by_created_at_desc()
    {
        $user          = User::factory()->createQuietly();
        $selector      = ('sqlite' === DB::connection()->getName()) ? 'select * from ' : '';
        $quote         = ('sqlite' === DB::connection()->getName()) ? '"' : '`';
        $expectedQuery = $selector . '(select MAX(created_at) viewed_at, oem_id as object_id, "oem" as object_type from '.$quote.'oem_detail_counter'.$quote.' where '.$quote.'user_id'.$quote.' = ? group by '.$quote.'oem_id'.$quote.', '.$quote.'user_id'.$quote.' order by '.$quote.'viewed_at'.$quote.' asc) union all ' . $selector . '(select MAX(created_at) viewed_at, part_id as object_id, "part" as object_type from '.$quote.'part_detail_counter'.$quote.' where '.$quote.'user_id'.$quote.' = ? group by '.$quote.'part_id'.$quote.', '.$quote.'user_id'.$quote.' order by '.$quote.'viewed_at'.$quote.' asc) order by '.$quote.'viewed_at'.$quote.' desc';

        $oemPartQuery = new OemPartQuery($user->getKey());

        $this->assertEquals($expectedQuery, $oemPartQuery->query()->toSql());
    }

    /** @test
     * @throws Exception
     */
    public function it_returns_a_paginated_recently_viewed_type_collection_with_object_related()
    {
        $user = User::factory()->create();

        $objectViewedFirst  = PartDetailCounter::factory()->usingUser($user)->create(['created_at' => Carbon::now()]);
        $objectViewedSecond = OemDetailCounter::factory()->usingUser($user)->create([
            'created_at' => Carbon::now()->subSeconds(20),
        ]);
        $objectViewedThird  = OemDetailCounter::factory()->usingUser($user)->create([
            'created_at' => Carbon::now()->subSeconds(10),
        ]);

        $oemPartQuery = new OemPartQuery($user->getKey());

        $paginated = $oemPartQuery->paginate();
        $this->assertInstanceOf(LengthAwarePaginator::class, $paginated);

        $this->assertCount(3, $paginated);

        $expected = new Collection();
        $expected->push(new RecentlyViewed([
            'object_id'   => $objectViewedFirst->part_id,
            'object_type' => Part::MORPH_ALIAS,
            'object'      => $objectViewedFirst->part,
            'viewed_at'   => $objectViewedFirst->created_at,
        ]));
        $expected->push(new RecentlyViewed([
            'object_id'   => $objectViewedThird->oem_id,
            'object_type' => Oem::MORPH_ALIAS,
            'object'      => $objectViewedThird->oem,
            'viewed_at'   => $objectViewedThird->created_at,
        ]));
        $expected->push(new RecentlyViewed([
            'object_id'   => $objectViewedSecond->oem_id,
            'object_type' => Oem::MORPH_ALIAS,
            'object'      => $objectViewedSecond->oem,
            'viewed_at'   => $objectViewedSecond->created_at,
        ]));

        $this->assertEquals($expected->toArray(), $paginated->items());
    }
}
