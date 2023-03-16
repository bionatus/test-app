<?php

namespace Tests\Unit\Models\User\Scopes;

use App\Models\User;
use App\Models\User\Scopes\ByPublicName;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByPublicNameTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * @dataProvider dataProvider
     */
    public function it_filters_users_by_public_name(int $expected, string $searchString)
    {
        $user       = ['first_name' => 'first', 'last_name' => 'last', 'public_name' => 'nickname'];
        $johnDoe    = ['first_name' => 'John', 'last_name' => 'Doe', 'public_name' => 'JohnDoe'];
        $joeAverage = ['first_name' => 'Joe', 'last_name' => 'Average', 'public_name' => 'JoeAverage'];
        $richardRoe = ['first_name' => 'Richard', 'last_name' => 'Roe', 'public_name' => 'RichardRoe'];

        User::factory()->count(3)->state(new Sequence($user, $johnDoe, $joeAverage, $richardRoe))->create();

        $filtered = User::scoped(new ByPublicName($searchString))->get();

        $this->assertCount($expected, $filtered);
    }

    public function dataProvider(): array
    {
        return [
            [0, 'first'],
            [0, 'last'],
            [0, 'first last'],
            [0, 'st la'],
            [1, 'nick'],
            [1, 'name'],
            [1, 'nickname'],
            [0, 'other'],
        ];
    }
}
