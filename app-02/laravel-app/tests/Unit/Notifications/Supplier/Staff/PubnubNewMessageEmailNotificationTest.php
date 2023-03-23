<?php

namespace Tests\Unit\Notifications\Supplier\Staff;

use App\Mail\Supplier\NewMessageEmail;
use App\Models\Setting;
use App\Models\SettingStaff;
use App\Models\Staff;
use App\Models\Supplier;
use App\Models\User;
use App\Notifications\Supplier\Staff\PubnubNewMessageEmailNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

class PubnubNewMessageEmailNotificationTest extends TestCase
{
    use RefreshDatabase;

    private $settingEmail;

    protected function setUp(): void
    {
        parent::setUp();
        $this->settingEmail = Setting::factory()
            ->applicableToStaff()
            ->create(['slug' => Setting::SLUG_STAFF_EMAIL_NOTIFICATION, 'value' => true]);
    }

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(PubnubNewMessageEmailNotification::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_uses_database_connection()
    {
        $job = new PubnubNewMessageEmailNotification(new Supplier(), new User(), 'Test message', new Staff(),
            true);

        $this->assertEquals('database', $job->connection);
    }

    /** @test
     * @dataProvider emailChannelProvider
     */
    public function it_is_sent_via_email_channel_if_requirements_are_met(
        $expected,
        $hasEmail,
        $setting,
        $shouldSendSupplierEmail
    ) {
        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $staff    = Staff::factory()->usingSupplier($supplier)->counter()->create();

        if ($hasEmail) {
            $staff->email = 'example@devbase.us';
        }

        SettingStaff::factory()->usingStaff($staff)->usingSetting($this->settingEmail)->create([
            'value' => $setting,
        ]);

        $notification = new PubnubNewMessageEmailNotification($supplier, $user, 'Test Message', $staff,
            $shouldSendSupplierEmail);

        $this->assertEquals($expected, in_array('mail', $notification->via($staff)));
    }

    public function emailChannelProvider(): array
    {
        return [
            //expected, hasEmail, setting, shouldSendSupplierEmail
            [true, true, true, true],
            [false, true, true, false],
            [false, true, false, true],
            [false, true, false, false],
            [false, false, true, true],
            [false, false, true, false],
            [false, false, false, true],
            [false, false, false, false],
        ];
    }

    /** @test */
    public function it_has_correct_email_recipients()
    {
        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $staff    = Staff::factory()->usingSupplier($supplier)->create([
            'name'            => 'John Doe',
            'email'           => 'example@example.com',
            'secondary_email' => 'secondary@example.com',
        ]);
        $message  = 'This is a test message';

        $notification = new PubnubNewMessageEmailNotification($supplier, $user, $message, $staff, true);

        $mail = $notification->toMail($staff);

        $this->assertEquals([
            [
                'name'    => 'John Doe',
                'address' => 'example@example.com',
            ],
        ], $mail->to);

        $this->assertEquals([
            [
                'name'    => 'John Doe',
                'address' => 'secondary@example.com',
            ],
        ], $mail->bcc);
    }

    /** @test */
    public function it_sends_an_order_creation_email()
    {
        $supplier = Mockery::mock(Supplier::class);
        $supplier->shouldReceive('getKey')->andReturn(1);

        $user = Mockery::mock(User::class);
        $user->shouldReceive('getKey')->andReturn(1);

        $staff = Mockery::mock(Staff::class);
        $staff->shouldReceive('getAttribute')->with('name')->andReturn('John Doe');
        $staff->shouldReceive('getAttribute')->with('email')->andReturn('example@example.com');
        $staff->shouldReceive('getAttribute')->with('secondary_email')->andReturn('example@example.com');


        $notification = new PubnubNewMessageEmailNotification($supplier, $user, 'test message', $staff, true);

        $this->assertInstanceOf(NewMessageEmail::class, $notification->toMail($staff));
    }
}
