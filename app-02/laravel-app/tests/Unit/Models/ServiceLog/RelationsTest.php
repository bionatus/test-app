<?php

namespace Tests\Unit\Models\ServiceLog;

use App\Models\ServiceLog;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property ServiceLog $instance
 */
class RelationsTest extends RelationsTestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_a_causer_user()
    {
        $user       = User::factory()->create();
        $serviceLog = ServiceLog::factory()->usingUser($user)->create();
        $causer     = $serviceLog->causer()->first();

        $this->assertInstanceOf(User::class, $causer);
    }

    /** @test */
    public function it_has_a_causer_supplier()
    {
        $supplier   = Supplier::factory()->createQuietly();
        $serviceLog = ServiceLog::factory()->usingSupplier($supplier)->create();
        $causer     = $serviceLog->causer()->first();

        $this->assertInstanceOf(Supplier::class, $causer);
    }
}
