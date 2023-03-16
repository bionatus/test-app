<?php

namespace Tests\Unit\Actions\Models\Cart;

use App;
use App\Actions\Models\Cart\DefaultSupplier;
use App\Actions\Models\Cart\GetCart;
use App\Models\Cart;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class GetCartTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_the_cart_if_exist()
    {
        $user     = User::factory()->create();
        $expected = Cart::factory()->usingUser($user)->create();

        $cart = App::make(GetCart::class, ['user' => $user])->execute();

        $this->assertSame($expected->getKey(), $cart->getKey());
    }

    /** @test */
    public function it_creates_the_cart_if_not_exist()
    {
        $user = User::factory()->create();

        App::make(GetCart::class, ['user' => $user])->execute();

        $this->assertDatabaseHas(Cart::tableName(), [
            'user_id' => $user->getKey(),
        ]);
    }

    /** @test */
    public function it_calls_default_supplier_action_when_cart_not_exist()
    {
        $user = User::factory()->create();

        $action = Mockery::mock(DefaultSupplier::class);
        $action->shouldReceive('execute')->withNoArgs()->once();
        App::bind(DefaultSupplier::class, fn() => $action);

        App::make(GetCart::class, ['user' => $user])->execute();
    }
}
