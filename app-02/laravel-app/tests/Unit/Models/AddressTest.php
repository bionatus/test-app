<?php

namespace Tests\Unit\Models;

use App\Models\Address;

class AddressTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(Address::tableName(), [
            'id',
            'address_1',
            'address_2',
            'city',
            'state',
            'country',
            'zip_code',
            'latitude',
            'longitude',
            'created_at',
            'updated_at',
        ]);
    }
}
