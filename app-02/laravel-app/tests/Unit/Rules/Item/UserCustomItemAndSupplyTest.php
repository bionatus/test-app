<?php

namespace Tests\Unit\Rules\Item;

use App\Models\CustomItem;
use App\Models\Item;
use App\Models\User;
use App\Rules\Item\UserCustomItemAndSupply;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserCustomItemAndSupplyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->login($this->user);
    }

    /** @test */
    public function it_returns_a_custom_message()
    {
        $rule = new UserCustomItemAndSupply();

        $this->assertSame('The item should exist and be type supply or custom item added by the technician.',
            $rule->message());
    }

    /** @test */
    public function it_fails_if_there_is_not_valid_item_order()
    {
        $rule = new UserCustomItemAndSupply();

        $this->assertFalse($rule->passes('attribute', 'invalid'));
    }

    /** @test */
    public function it_fails_with_a_part_type()
    {
        $partItem = Item::factory()->part()->create();

        $rule = new UserCustomItemAndSupply();

        $this->assertFalse($rule->passes('attribute', $partItem->getRouteKey()));
    }

    /** @test */
    public function it_fails_with_a_supplier_custom_item_type()
    {
        $supplierCustomItem = CustomItem::factory()->create(['creator_type' => 'supplier']);

        $rule = new UserCustomItemAndSupply();

        $this->assertFalse($rule->passes('attribute', $supplierCustomItem->item->getRouteKey()));
    }

    /** @test */
    public function it_passes_with_a_supply_type()
    {
        $item = Item::factory()->supply()->create();

        $rule = new UserCustomItemAndSupply();

        $this->assertTrue($rule->passes('attribute', $item->getRouteKey()));
    }

    /** @test */
    public function it_passes_with_a_custom_item_type()
    {
        $userCustomItem = CustomItem::factory()->create();

        $rule = new UserCustomItemAndSupply();

        $this->assertTrue($rule->passes('attribute', $userCustomItem->item->getRouteKey()));
    }
}
