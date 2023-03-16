<?php

namespace Tests\Unit\Models\SettingSupplier;

use App\Models\Setting;
use App\Models\SettingSupplier;
use App\Models\Supplier;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property SettingSupplier $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = SettingSupplier::factory()->usingSupplier(Supplier::factory()->createQuietly())->create();
    }

    /** @test */
    public function it_belongs_to_a_supplier()
    {
        $related = $this->instance->supplier()->first();

        $this->assertInstanceOf(Supplier::class, $related);
    }

    /** @test */
    public function it_belongs_to_a_setting()
    {
        $related = $this->instance->setting()->first();

        $this->assertInstanceOf(Setting::class, $related);
    }
}
