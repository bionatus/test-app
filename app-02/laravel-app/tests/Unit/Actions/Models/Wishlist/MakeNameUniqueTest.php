<?php

namespace Tests\Unit\Actions\Models\Wishlist;

use App\Actions\Models\Wishlist\MakeNameUnique;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MakeNameUniqueTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_adds_a_consecutive_number_if_name_already_exists()
    {
        $name = 'Fake wishlist';
        $user = User::factory()->create();
        Wishlist::factory()->usingUser($user)->create(['name' => $name]);

        $result = (new MakeNameUnique())->execute($user, $name);

        $this->assertSame($name . ' 2', $result);
    }

    /** @test */
    public function it_does_not_add_a_consecutive_number_if_name_is_not_already_used()
    {
        $name = 'Fake wishlist';
        $user = User::factory()->create();
        Wishlist::factory()->usingUser($user)->create(['name' => 'other wishlist name']);

        $result = (new MakeNameUnique())->execute($user, $name);

        $this->assertSame($name, $result);
    }

    /** @test */
    public function it_returns_the_same_name_if_user_does_not_have_any_wishlist()
    {
        $name = 'Fake wishlist';
        $user = User::factory()->create();

        $result = (new MakeNameUnique())->execute($user, $name);

        $this->assertSame($name, $result);
    }
}
