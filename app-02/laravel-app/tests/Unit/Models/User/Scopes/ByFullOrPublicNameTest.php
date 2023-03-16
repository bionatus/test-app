<?php

namespace Tests\Unit\Models\User\Scopes;

use App\Models\User;
use App\Models\User\Scopes\ByFullOrPublicName;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByFullOrPublicNameTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * @dataProvider dataProvider
     */
    public function it_filters_users_by_first_name_or_last_name_or_public_name(int $expected, string $searchString)
    {
        User::factory()->create(['first_name' => 'first', 'last_name' => 'last', 'public_name' => 'nickname']);

        $johnDoe    = ['first_name' => 'John', 'last_name' => 'Doe', 'public_name' => 'JohnDoe'];
        $joeAverage = ['first_name' => 'Joe', 'last_name' => 'Average', 'public_name' => 'JoeAverage'];
        $richardRoe = ['first_name' => 'Richard', 'last_name' => 'Roe', 'public_name' => 'RichardRoe'];
        User::factory()->count(3)->state(new Sequence($johnDoe, $joeAverage, $richardRoe))->create();

        $filtered = User::scoped(new ByFullOrPublicName($searchString))->get();

        $this->assertCount($expected, $filtered);
    }

    public function dataProvider()
    {
        return [
            [1, 'first'],
            [1, 'last'],
            [1, 'first last'],
            [1, 'st la'],
            [1, 'nick'],
            [1, 'name'],
            [0, 'other'],
        ];
    }
}
