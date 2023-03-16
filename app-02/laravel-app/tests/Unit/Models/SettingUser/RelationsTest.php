<?php

namespace Tests\Unit\Models\SettingUser;

use App\Models\Setting;
use App\Models\SettingUser;
use App\Models\User;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property SettingUser $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = SettingUser::factory()->create();
    }

    /** @test */
    public function it_belongs_to_a_user()
    {
        $related = $this->instance->user()->first();

        $this->assertInstanceOf(User::class, $related);
    }

    /** @test */
    public function it_belongs_to_a_setting()
    {
        $related = $this->instance->setting()->first();

        $this->assertInstanceOf(Setting::class, $related);
    }
}
