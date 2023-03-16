<?php

namespace Tests\Unit\Models\Setting;

use App\Models\Setting;
use App\Models\SettingStaff;
use App\Models\SettingSupplier;
use App\Models\SettingUser;
use App\Models\User;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property Setting $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = Setting::factory()->create();
    }

    /** @test */
    public function it_has_users()
    {
        SettingUser::factory()->usingSetting($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->users()->get();

        $this->assertCorrectRelation($related, User::class);
    }

    /** @test */
    public function it_has_setting_users()
    {
        SettingUser::factory()->usingSetting($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->settingUsers()->get();

        $this->assertCorrectRelation($related, SettingUser::class);
    }

    /** @test */
    public function it_has_setting_suppliers()
    {
        SettingSupplier::factory()->usingSetting($this->instance)->count(self::COUNT)->createQuietly();

        $related = $this->instance->settingSuppliers()->get();

        $this->assertCorrectRelation($related, SettingSupplier::class);
    }

    /** @test */
    public function it_has_setting_staffs()
    {
        SettingStaff::factory()->usingSetting($this->instance)->count(self::COUNT)->createQuietly();

        $related = $this->instance->settingStaffs()->get();

        $this->assertCorrectRelation($related, SettingStaff::class);
    }
}
