<?php

namespace Tests\Unit\Models\SupplierUser\Scopes;

use App\Models\Supplier;
use App\Models\SupplierUser;
use App\Models\SupplierUser\Scopes\ByVisibleByUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByVisibleByUserTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * @dataProvider dataProvider
     */
    public function it_filters_by_visible_by_user($visibility)
    {
        $supplier                = Supplier::factory()->createQuietly();
        $notVisibleSupplierUsers = SupplierUser::factory()->usingSupplier($supplier)->notVisible()->count(10)->create();
        $visibleSupplierUsers      = SupplierUser::factory()->usingSupplier($supplier)->count(10)->create();

        $filtered = SupplierUser::scoped(new ByVisibleByUser($visibility));

        $keyName = SupplierUser::keyName();
        if ($visibility){
            $this->assertEqualsCanonicalizing($visibleSupplierUsers->pluck($keyName), $filtered->pluck($keyName));
        } else {
            $this->assertEqualsCanonicalizing($notVisibleSupplierUsers->pluck($keyName), $filtered->pluck($keyName));
        }
    }

    public function dataProvider():array
    {
        return [
            [false],
            [true],
        ];
    }
}
