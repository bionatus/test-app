<?php

namespace Tests\Unit\Models;

use App\Models\Setting;

class SettingTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(Setting::tableName(), [
            'id',
            'name',
            'slug',
            'group',
            'applicable_to',
            'type',
            'value',
        ]);
    }

    /** @test */
    public function it_uses_slug()
    {
        $setting = Setting::factory()->create(['slug' => 'something']);

        $this->assertEquals($setting->slug, $setting->getRouteKey());
    }

    /** @test */
    public function it_fills_slug_on_creation()
    {
        $setting = Setting::factory()->make(['slug' => null]);
        $setting->save();

        $this->assertNotNull($setting->slug);
    }

    /** @test */
    public function it_cast_boolean_values()
    {
        $setting1 = Setting::factory()->boolean()->make(['value' => '1']);
        $setting2 = Setting::factory()->boolean()->make(['value' => '0']);

        $this->assertTrue($setting1->value);
        $this->assertFalse($setting2->value);
    }

    /** @test */
    public function it_cast_integer_values()
    {
        $setting1 = Setting::factory()->integer()->make(['value' => '1.99']);
        $setting2 = Setting::factory()->integer()->make(['value' => '0']);
        $setting3 = Setting::factory()->integer()->make(['value' => 'invalid']);

        $this->assertSame(1, $setting1->value);
        $this->assertSame(0, $setting2->value);
        $this->assertSame(0, $setting3->value);
    }

    /** @test */
    public function it_cast_double_values()
    {
        $setting1 = Setting::factory()->double()->make(['value' => '1.3']);
        $setting2 = Setting::factory()->double()->make(['value' => '0.4']);

        $this->assertSame(1.3, $setting1->value);
        $this->assertSame(0.4, $setting2->value);
    }

    /** @test */
    public function it_cast_string_values()
    {
        $setting1 = Setting::factory()->string()->make(['value' => 34]);
        $setting2 = Setting::factory()->string()->make(['value' => false]);

        $this->assertSame('34', $setting1->value);
        $this->assertSame('', $setting2->value);
    }

    /** @test */
    public function it_knows_if_its_group_is_agent()
    {
        $agentSetting   = Setting::factory()->groupAgent()->make();
        $noAgentSetting = Setting::factory()->groupNotification()->make();

        $this->assertTrue($agentSetting->isGroupAgent());
        $this->assertFalse($noAgentSetting->isGroupAgent());
    }
}
