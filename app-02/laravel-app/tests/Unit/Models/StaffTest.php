<?php

namespace Tests\Unit\Models;

use App;
use App\Models\HasSetting;
use App\Models\PerformsOemSearches;
use App\Models\PerformsPartSearches;
use App\Models\Staff;
use App\Models\Supplier;
use App\Notifications\LiveApi\V1\ResetPasswordNotification;
use App\Services\Hubspot\Hubspot;
use Exception;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Mockery;
use Notification;
use ReflectionClass;
use ReflectionException;
use Tymon\JWTAuth\Contracts\JWTSubject;

class StaffTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(Staff::tableName(), [
            'id',
            'supplier_id',
            'uuid',
            'type',
            'name',
            'email',
            'password',
            'phone',
            'secondary_email',
            'initial_password_set_at',
            'tos_accepted_at',
            'created_at',
            'updated_at',
        ]);
    }

    /** @test */
    public function its_type_constants_are_an_specific_string()
    {
        $this->assertSame('accountant', Staff::TYPE_ACCOUNTANT);
        $this->assertSame('contact', Staff::TYPE_CONTACT);
        $this->assertSame('counter', Staff::TYPE_COUNTER);
        $this->assertSame('manager', Staff::TYPE_MANAGER);
        $this->assertSame('owner', Staff::TYPE_OWNER);
    }

    /** @test */
    public function it_uses_uuid()
    {
        $staff = Staff::factory()->createQuietly(['uuid' => Str::uuid()->toString()]);

        $this->assertEquals($staff->uuid, $staff->getRouteKey());
    }

    /** @test */
    public function it_fills_uuid_on_creation()
    {
        $hubspot = Mockery::mock(Hubspot::class);
        $hubspot->shouldReceive('upsertCompany')->withAnyArgs()->once()->andReturnNull();
        App::bind(Hubspot::class, fn() => $hubspot);

        $staff = Staff::factory()->make(['uuid' => null]);
        $staff->save();

        $this->assertNotNull($staff->uuid);
    }

    /** @test */
    public function it_knows_if_has_set_initial_password()
    {
        $hubspot = Mockery::mock(Hubspot::class);
        $hubspot->shouldReceive('upsertCompany')->withAnyArgs()->twice()->andReturnNull();
        App::bind(Hubspot::class, fn() => $hubspot);

        $notSet = Staff::factory()->make(['initial_password_set_at' => null]);
        $set    = Staff::factory()->make(['initial_password_set_at' => Carbon::now()]);

        $this->assertFalse($notSet->hasSetInitialPassword());
        $this->assertTrue($set->hasSetInitialPassword());
    }

    /** @test */
    public function it_uses_phone_field_for_twilio_channel_notifications()
    {
        $phone    = 123456789;
        $staff = Staff::factory()->createQuietly(['phone' => $phone]);

        $this->assertEquals($phone, $staff->routeNotificationForTwilio());
    }

    /** @test */
    public function it_implements_interfaces()
    {
        $reflection = new ReflectionClass(Staff::class);

        $this->assertTrue($reflection->implementsInterface(JWTSubject::class));
        $this->assertTrue($reflection->implementsInterface(CanResetPasswordContract::class));
        $this->assertTrue($reflection->implementsInterface(PerformsOemSearches::class));
        $this->assertTrue($reflection->implementsInterface(PerformsPartSearches::class));
        $this->assertTrue($reflection->implementsInterface(HasSetting::class));
    }

    /** @test
     * @throws ReflectionException
     */
    public function it_uses_can_reset_password_trait()
    {
        $this->assertUseTrait(Staff::class, CanResetPassword::class, ['sendPasswordResetNotification']);
    }

    /** @test
     * @throws Exception
     */
    public function it_can_send_password_reset_notification()
    {
        Notification::fake();

        $staff = Staff::factory()->createQuietly();

        $staff->sendPasswordResetNotification($token = 'token');

        Notification::assertSentTo($staff, ResetPasswordNotification::class,
            function(ResetPasswordNotification $notification) use ($token) {
                $this->assertEquals($token, $notification->token);

                return true;
            });
    }

    /** @test
     * @throws ReflectionException
     */
    public function it_uses_notifiable_trait()
    {
        $this->assertUseTrait(Staff::class, Notifiable::class);
    }

    /** @test */
    public function it_knows_if_it_is_counter()
    {
        $supplier     = Supplier::factory()->createQuietly();
        $counterStaff = Staff::factory()->usingSupplier($supplier)->counter()->create();
        $ownerStaff   = Staff::factory()->usingSupplier($supplier)->owner()->create();

        $this->assertTrue($counterStaff->isCounter());
        $this->assertFalse($ownerStaff->isCounter());
    }
}
