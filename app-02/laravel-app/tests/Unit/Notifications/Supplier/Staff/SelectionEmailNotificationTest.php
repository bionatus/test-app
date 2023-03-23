<?php

namespace Tests\Unit\Notifications\Supplier\Staff;

use App\Mail\Supplier\SelectionEmail;
use App\Models\Setting;
use App\Models\SettingStaff;
use App\Models\Staff;
use App\Models\Supplier;
use App\Notifications\Supplier\Staff\SelectionEmailNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

class SelectionEmailNotificationTest extends TestCase
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
        $reflection = new ReflectionClass(SelectionEmailNotification::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_uses_database_connection()
    {
        $job = new SelectionEmailNotification(new Staff(), new Supplier(), true);

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
        $supplier = Supplier::factory()->createQuietly();
        $staff    = Staff::factory()->usingSupplier($supplier)->counter()->create();

        if ($hasEmail) {
            $staff->email = 'example@devbase.us';
        }

        SettingStaff::factory()->usingStaff($staff)->usingSetting($this->settingEmail)->create([
            'value' => $setting,
        ]);

        $notification = new SelectionEmailNotification($staff, $supplier, $shouldSendSupplierEmail);

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
        $supplier = Supplier::factory()->createQuietly();
        $staff    = Staff::factory()->usingSupplier($supplier)->create([
            'name'            => 'John Doe',
            'email'           => 'example@example.com',
            'secondary_email' => 'secondary@example.com',
        ]);

        $notification = new SelectionEmailNotification($staff, $supplier, true);

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

        $staff = Mockery::mock(Staff::class);
        $staff->shouldReceive('getAttribute')->with('name')->andReturn('John Doe');
        $staff->shouldReceive('getAttribute')->with('email')->andReturn('example@example.com');
        $staff->shouldReceive('getAttribute')->with('secondary_email')->andReturn('example@example.com');


        $notification = new SelectionEmailNotification($staff, $supplier,  true);

        $this->assertInstanceOf(SelectionEmail::class, $notification->toMail($staff));
    }
}
