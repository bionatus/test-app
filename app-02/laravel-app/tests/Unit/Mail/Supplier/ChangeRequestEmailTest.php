<?php

namespace Tests\Unit\Mail\Supplier;

use App\Constants\SupplierChangeRequestReasons;
use App\Mail\Supplier\ChangeRequestEmail;
use App\Models\Supplier;
use App\Models\User;
use App\Types\CountryDataType;
use Illuminate\Contracts\Queue\ShouldQueue;
use MenaraSolutions\Geographer\Country;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

class ChangeRequestEmailTest extends TestCase
{
    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(ChangeRequestEmail::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_uses_database_connection()
    {
        $job = new ChangeRequestEmail(new Supplier(), new User(), '');

        $this->assertEquals('database', $job->connection);
    }

    /** @test */
    public function it_shows_correct_fields()
    {
        $country = Country::build(CountryDataType::UNITED_STATES);
        $state   = $country->getStates()->first();

        $supplier = Mockery::mock(Supplier::class);
        $supplier->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = 'id');
        $supplier->shouldReceive('getAttribute')->withArgs(['name'])->once()->andReturn($name = 'Name');
        $supplier->shouldReceive('getAttribute')->withArgs(['address'])->once()->andReturn($address = 'address');
        $supplier->shouldReceive('getAttribute')->withArgs(['address_2'])->once()->andReturn($address2 = 'address 2');
        $supplier->shouldReceive('getAttribute')->withArgs(['city'])->once()->andReturn($city = 'city');
        $supplier->shouldReceive('getAttribute')->withArgs(['state'])->once()->andReturn($state->code);
        $supplier->shouldReceive('getAttribute')->withArgs(['country'])->once()->andReturn($country->code);
        $supplier->shouldReceive('getAttribute')->withArgs(['zip_code'])->once()->andReturn($zipCode = '12345');
        $supplier->shouldReceive('getAttribute')->withArgs(['latitude'])->once()->andReturn($latitude = '222');
        $supplier->shouldReceive('getAttribute')->withArgs(['longitude'])->once()->andReturn($longitude = '810');

        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->withArgs(['first_name'])->once()->andReturn($firstName = 'Jon');
        $user->shouldReceive('getAttribute')->withArgs(['last_name'])->once()->andReturn($lastName = 'Doe');
        $user->shouldReceive('getAttribute')->withArgs(['email'])->once()->andReturn($email = 'jon@doe.com');

        $reason = SupplierChangeRequestReasons::REASON_OTHER;
        $detail = 'Lorem ipsum.';

        $mailable = new ChangeRequestEmail($supplier, $user, $reason, $detail);

        $mailable->assertSeeInHtml($id);
        $mailable->assertSeeInHtml($name);
        $mailable->assertSeeInHtml($address);
        $mailable->assertSeeInHtml($address2);
        $mailable->assertSeeInHtml($city);
        $mailable->assertSeeInHtml($state->getName());
        $mailable->assertSeeInHtml($country->getName());
        $mailable->assertSeeInHtml($zipCode);
        $mailable->assertSeeInHtml($latitude);
        $mailable->assertSeeInHtml($longitude);
        $mailable->assertSeeInHtml($firstName . ' ' . $lastName);
        $mailable->assertSeeInHtml($email);
        $mailable->assertSeeInHtml($reason);
        $mailable->assertSeeInHtml($detail);
    }
}
