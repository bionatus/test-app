<?php

namespace Tests\Unit\Http\Resources\LiveApi\V1\Supplier;

use App\Http\Resources\LiveApi\V1\Supplier\CounterStaffResource;
use App\Models\Setting;
use App\Models\SettingStaff;
use App\Models\Staff;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Mockery;
use Tests\TestCase;

class CounterStaffResourceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_fields()
    {
        $settingEmail = Setting::factory()
            ->boolean()
            ->applicableToStaff()
            ->create(['slug' => Setting::SLUG_STAFF_EMAIL_NOTIFICATION]);

        $settingSms = Setting::factory()
            ->boolean()
            ->applicableToStaff()
            ->create(['slug' => Setting::SLUG_STAFF_SMS_NOTIFICATION]);

        $settingStaffEmail = Mockery::mock(SettingStaff::class);
        $settingStaffEmail->shouldReceive('getAttribute')->with('value')->once()->andReturnTrue();
        $settingStaffEmail->shouldReceive('getAttribute')->with('setting')->andReturn($settingEmail);
        $settingStaffSms = Mockery::mock(SettingStaff::class);
        $settingStaffSms->shouldReceive('getAttribute')->with('value')->once()->andReturnTrue();
        $settingStaffSms->shouldReceive('getAttribute')->with('setting')->andReturn($settingSms);

        $settingStaffs = Collection::make([$settingStaffEmail, $settingStaffSms]);

        $staff = Mockery::mock(Staff::class);
        $staff->shouldReceive('getAttribute')->with('name')->once()->andReturn($name = 'John');
        $staff->shouldReceive('getAttribute')->with('email')->once()->andReturn($email = 'john@email.com');
        $staff->shouldReceive('getAttribute')->with('phone')->once()->andReturn($phone = '59170364108');
        $staff->shouldReceive('getAttribute')->with('settingStaffs')->once()->andReturn($settingStaffs);

        $resource = new CounterStaffResource($staff);

        $response = $resource->resolve();

        $data = [
            'name'               => $name,
            'email'              => $email,
            'phone'              => $phone,
            'sms_notification'   => true,
            'email_notification' => true,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(CounterStaffResource::jsonSchema(), false, false);

        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_null_values()
    {
        Setting::factory()->boolean()->applicableToStaff()->create([
            'slug'  => Setting::SLUG_STAFF_EMAIL_NOTIFICATION,
            'value' => '0',
        ]);

        Setting::factory()->boolean()->applicableToStaff()->create([
            'slug'  => Setting::SLUG_STAFF_SMS_NOTIFICATION,
            'value' => '0',
        ]);

        $staff = Mockery::mock(Staff::class);
        $staff->shouldReceive('getAttribute')->with('name')->once()->andReturn($name = 'John');
        $staff->shouldReceive('getAttribute')->with('email')->once()->andReturn($email = null);
        $staff->shouldReceive('getAttribute')->with('phone')->once()->andReturn($phone = null);
        $staff->shouldReceive('getAttribute')->with('settingStaffs')->once()->andReturn(Collection::make([]));

        $resource = new CounterStaffResource($staff);

        $response = $resource->resolve();

        $data = [
            'name'               => $name,
            'email'              => $email,
            'phone'              => $phone,
            'email_notification' => false,
            'sms_notification'   => false,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(CounterStaffResource::jsonSchema(), false, false);

        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
