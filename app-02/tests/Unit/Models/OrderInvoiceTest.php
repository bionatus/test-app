<?php

namespace Tests\Unit\Models;

use App\Models\OrderInvoice;

class OrderInvoiceTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(OrderInvoice::tableName(), [
            'id',
            'order_id',
            'number',
            'type',
            'subtotal',
            'take_rate',
            'bid_number',
            'order_name',
            'payment_terms',
            'processed_at',
            'created_at',
            'updated_at',
        ]);
    }
}
