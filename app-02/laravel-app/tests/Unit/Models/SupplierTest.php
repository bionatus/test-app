<?php

namespace Tests\Unit\Models;

use App;
use App\Constants\MediaCollectionNames;
use App\Models\Flag;
use App\Models\ForbiddenZipCode;
use App\Models\HasSetting;
use App\Models\HasState;
use App\Models\HasUuid;
use App\Models\Supplier;
use App\Models\SupplierHour;
use App\Services\Hubspot\Hubspot;
use App\Traits\HasFlags;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Mockery;
use ReflectionClass;
use ReflectionException;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class SupplierTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(Supplier::tableName(), [
            'id',
            'uuid',
            'airtable_id',
            'hubspot_id',
            'name',
            'branch',
            'address',
            'address_2',
            'city',
            'state',
            'zip_code',
            'country',
            'latitude',
            'longitude',
            'phone',
            'fax',
            'prokeep_phone',
            'email',
            'phone',
            'contact_name',
            'contact_email',
            'contact_secondary_email',
            'contact_phone',
            'contact_job',
            'url',
            'about',
            'image',
            'offers_delivery',
            'timezone',
            'monday_hours',
            'tuesday_hours',
            'wednesday_hours',
            'thursday_hours',
            'friday_hours',
            'saturday_hours',
            'sunday_hours',
            'verified_at',
            'welcome_displayed_at',
            'take_rate',
            'take_rate_until',
            'terms',
            'supplier_company_id',
            'published_at',
            'created_at',
            'updated_at',
        ]);
    }

    /** @test */
    public function it_implements_interfaces()
    {
        $reflection = new ReflectionClass(Supplier::class);

        $this->assertTrue($reflection->implementsInterface(HasMedia::class));
        $this->assertTrue($reflection->implementsInterface(HasSetting::class));
    }

    /** @test
     * @throws ReflectionException
     */
    public function it_uses_traits()
    {
        $this->assertUseTrait(Supplier::class, HasFlags::class);
        $this->assertUseTrait(Supplier::class, HasUuid::class);
        $this->assertUseTrait(Supplier::class, HasState::class);
        $this->assertUseTrait(Supplier::class, InteractsWithMedia::class, ['registerMediaCollections']);
        $this->assertUseTrait(Supplier::class, Notifiable::class);
    }

    /** @test */
    public function it_uses_uuid()
    {
        $supplier = Supplier::factory()->createQuietly(['uuid' => Str::uuid()->toString()]);

        $this->assertEquals($supplier->uuid, $supplier->getRouteKey());
    }

    /** @test */
    public function it_fills_uuid_on_creation()
    {
        $hubspot = Mockery::mock(Hubspot::class);
        $hubspot->shouldReceive('upsertCompany')->withAnyArgs()->once()->andReturnNull();
        App::bind(Hubspot::class, fn() => $hubspot);

        $supplier = Supplier::factory()->make(['uuid' => null]);
        $supplier->save();

        $this->assertNotNull($supplier->uuid);
    }

    /** @test
     * @dataProvider zipCode
     */
    public function it_knows_if_it_is_in_a_curri_valid_zip_code(
        string $forbiddenZipCode,
        ?string $supplierZipCode,
        bool $resultExpected
    ) {
        ForbiddenZipCode::factory()->create(['zip_code' => $forbiddenZipCode]);
        $supplier = Supplier::factory()->createQuietly(['zip_code' => $supplierZipCode]);

        $this->assertSame($supplier->isOnCurriValidZipCode(),$resultExpected);
    }

    public function zipCode(): array
    {
        return [
            ['2222', '1111', true],
            ['1111', '1111', false],
            ['3333', null, false]
        ];
    }

    /** @test
     * @dataProvider infoProvider
     */
    public function its_curri_delivery_disabled_or_enabled_depending_forbidden_zip_code_and_flag(
        string $forbiddenZipCode,
        ?string $supplierZipCode,
        bool $flagForbiddenCurri,
        bool $resulteExpected
    ) {
        ForbiddenZipCode::factory()->create(['zip_code' => $forbiddenZipCode]);
        $supplier = Supplier::factory()->createQuietly(['zip_code' => $supplierZipCode]);
        if ($flagForbiddenCurri) {
            Flag::factory()->usingModel($supplier)->create(['name' => Flag::FORBIDDEN_CURRI]);
        }

        $this->assertSame($supplier->isCurriDeliveryEnabled(), $resulteExpected);
    }

    public function infoProvider(): array
    {
        return [
            ['2222', '1111', false, true],
            ['1111', '1111', false, false],
            ['2222', '1111', true, false],
            ['1111', '1111', true, false],
            ['1111', null, false, false],
            ['1111', null, true, false],
        ];
    }

    /** @test */
    public function it_can_verify_a_supplier()
    {
        $supplier = Supplier::factory()->make(['verified_at' => null]);

        $supplier->verify();

        $this->assertNotNull($supplier->verified_at);
    }

    /** @test */
    public function it_does_not_change_verified_date_when_already_verified()
    {
        $supplier = Supplier::factory()->make(['verified_at' => $now = Carbon::now()->subMinute()]);

        $supplier->verify();

        $this->assertSame($now->toDateTimeString(), $supplier->verified_at->toDateTimeString());
    }

    /** @test */
    public function it_registers_media_collections()
    {
        $supplier = Supplier::factory()->createQuietly();
        $supplier->registerMediaCollections();

        $mediaCollectionNames = Collection::make($supplier->mediaCollections)->pluck('name');
        $this->assertContains(MediaCollectionNames::IMAGES, $mediaCollectionNames);
        $this->assertContains(MediaCollectionNames::LOGO, $mediaCollectionNames);
    }

    /** @test */
    public function it_returns_its_distance_attribute_if_it_exists()
    {
        $distance = 5.555555;
        $supplier = Supplier::factory()->make(['distance' => $distance]);

        $this->assertNotNull($supplier->getDistanceAttribute());
    }

    /** @test */
    public function it_returns_null_as_its_distance_attribute_if_it_does_not_exists()
    {
        $supplier = Supplier::factory()->make();

        $this->assertNull($supplier->getDistanceAttribute());
    }

    /** @test */
    public function it_returns_its_distance_attribute_rounded_to_two_decimals()
    {
        $distance = 5.555555;
        $supplier = Supplier::factory()->make(['distance' => $distance]);

        $this->assertNotNull($supplier->getDistanceAttribute());
        $this->assertEquals(round($distance, 2), $supplier->getDistanceAttribute());
    }

    /** @test */
    public function it_uses_notifiable_trait()
    {
        $this->assertUseTrait(Supplier::class, Notifiable::class, ['routeNotificationForTwilio']);
    }

    /** @test */
    public function it_uses_contact_phone_field_for_twilio_channel_notifications()
    {
        $phone    = 123456789;
        $supplier = Supplier::factory()->make(['contact_phone' => $phone]);

        $this->assertEquals($phone, $supplier->routeNotificationForTwilio());
    }

    /** @test */
    public function it_uses_prokeep_phone_field_for_twilio_by_prokeep_channel_notifications()
    {
        $prokeepPhone = 123456789;
        $supplier     = Supplier::factory()->make(['prokeep_phone' => $prokeepPhone]);

        $this->assertEquals($prokeepPhone, $supplier->routeNotificationForTwilioByProkeepPhone());
    }

    /**
     * @test
     * @dataProvider dataProvider
     */
    public function it_knows_if_it_is_in_working_hours(int $day, int $start, int $end, bool $expectedResult)
    {
        $from = $start ? Carbon::now()->addMinutes($start) : Carbon::now()->startOfDay();
        $to   = $end ? Carbon::now()->subMinutes($end) : Carbon::now()->endOfDay();

        $supplierHour = SupplierHour::factory()->createQuietly([
            'day'  => strtolower(Carbon::today()->addDays($day)->format('l')),
            'from' => $from->format('h:i a'),
            'to'   => $to->format('h:i a'),
        ]);

        $this->assertEquals($expectedResult, $supplierHour->supplier->isInWorkingHours());
    }

    public function dataProvider()
    {
        return [
            [
                'day'             => 0,
                'start'           => 0,
                'end'             => 0,
                'expected_result' => true,
            ],
            [
                'day'             => 0,
                'start'           => 5,
                'end'             => 0,
                'expected_result' => false,
            ],
            [
                'day'             => 0,
                'start'           => 0,
                'end'             => 5,
                'expected_result' => false,
            ],
            [
                'day'             => 1,
                'start'           => 0,
                'end'             => 0,
                'expected_result' => false,
            ],
        ];
    }

    /** @test */
    public function it_knows_if_has_contact_email()
    {
        $supplierWithoutContactEmail = Supplier::factory()->createQuietly(['contact_email' => null]);
        $supplierWithContactEmail    = Supplier::factory()->createQuietly(['contact_email' => 'fake-contact-email']);

        $this->assertFalse($supplierWithoutContactEmail->hasContactEmail());
        $this->assertTrue($supplierWithContactEmail->hasContactEmail());
    }
}
