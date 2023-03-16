<?php

namespace Tests\Unit\Rules\ItemOrder;

use App\Models\ItemOrder;
use App\Rules\ItemOrder\ValidQuantity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ValidQuantityTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_a_custom_message()
    {
        $rule = new ValidQuantity([]);

        $this->assertSame('Invalid quantity.', $rule->message());
    }

    /** @test */
    public function it_fails_when_quantity_is_greater_than_quantity_requested()
    {
        $itemOrder = ItemOrder::factory()->create();
        $items     = [
            [
                'uuid'     => $itemOrder->getRouteKey(),
                'quantity' => $quantity = $itemOrder->quantity_requested + 1,
            ],
        ];

        $rule = new ValidQuantity($items);

        $this->assertFalse($rule->passes('itemOrder.0.quantity', $quantity));
    }

    /** @test */
    public function it_passes_when_quantity_is_equals_or_less_than_quantity_requested()
    {
        $itemOrder = ItemOrder::factory()->create();
        $items     = [
            [
                'uuid'     => $itemOrder->getRouteKey(),
                'quantity' => $quantity = $itemOrder->quantity_requested,
            ],
        ];

        $rule = new ValidQuantity($items);

        $this->assertTrue($rule->passes('itemOrder.0.quantity', $quantity));
    }
}
