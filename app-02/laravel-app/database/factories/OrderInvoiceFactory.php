<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderInvoice;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|OrderInvoice create($attributes = [], ?Model $parent = null)
 * @method Collection|OrderInvoice createQuietly($attributes = [], ?Model $parent = null)
 * @method Collection|OrderInvoice make($attributes = [], ?Model $parent = null)
 */
class OrderInvoiceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_id'      => Order::factory(),
            'number'        => $this->faker->numberBetween(),
            'type'          => OrderInvoice::TYPE_INVOICE,
            'subtotal'      => $this->faker->numberBetween(),
            'take_rate'     => Supplier::DEFAULT_TAKE_RATE,
            'bid_number'    => $this->faker->swiftBicNumber,
            'payment_terms' => Supplier::DEFAULT_PAYMENT_TERMS,
        ];
    }

    public function credit(): self
    {
        return $this->state(function() {
            return [
                'type' => OrderInvoice::TYPE_CREDIT,
            ];
        });
    }

    public function invoice(): self
    {
        return $this->state(function() {
            return [
                'type' => OrderInvoice::TYPE_INVOICE,
            ];
        });
    }

    public function notProcessed(): self
    {
        return $this->state(function() {
            return [
                'processed_at' => null,
            ];
        });
    }

    public function processed(): self
    {
        return $this->state(function() {
            return [
                'processed_at' => Carbon::now(),
            ];
        });
    }

    public function usingOrder(Order $order): self
    {
        return $this->state(function() use ($order) {
            return [
                'order_id' => $order,
            ];
        });
    }
}
