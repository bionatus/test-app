<?php

namespace Tests\Unit\Models\User\Scopes;

use App\Models\User;
use App\Models\User\Scopes\ByFullName;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByFullNameTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * @dataProvider dataProvider
     */
    public function it_filters_users_by_first_name_or_last_name(int $expected, string $searchString)
    {
        User::factory()->create(['first_name' => 'first', 'last_name'=>'last']);
        User::factory()->count(3)->create();

        $filtered = User::scoped(new ByFullName($searchString))->get();

        $this->assertCount($expected, $filtered);
    }

    public function dataProvider()
    {
        return [
            [1, 'first'],
            [1, 'last'],
            [1, 'first last'],
            [1, 'st la'],
            [0, 'other'],
        ];
    }
}
