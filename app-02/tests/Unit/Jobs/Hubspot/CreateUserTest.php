<?php

namespace Tests\Unit\Jobs\Hubspot;

use App;
use App\Jobs\Hubspot\CreateUser;
use App\Models\User;
use App\Services\Hubspot\Hubspot;
use Exception;
use HubSpot\Client\Crm\Companies\Model\SimplePublicObjectInput as Company;
use HubSpot\Client\Crm\Contacts\Model\SimplePublicObjectInput as Contact;
use Illuminate\Contracts\Queue\ShouldQueue;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

class CreateUserTest extends TestCase
{
    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(CreateUser::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_uses_database_connection()
    {
        $job = new CreateUser(new User());

        $this->assertEquals('database', $job->connection);
    }

    /** @test */
    public function it_creates_a_contact_in_hubspot_and_sets_hubspot_id_in_user()
    {
        $hubspotId = 123;

        $user = Mockery::mock(User::class);
        $user->shouldReceive('setAttribute')->withArgs(['hubspot_id', $hubspotId])->once();
        $user->shouldReceive('save')->withNoArgs()->once();

        $contact = Mockery::mock(Contact::class);
        $contact->shouldReceive('getId')->withNoArgs()->times(4)->andReturn($hubspotId);

        $company = Mockery::mock(Company::class);
        $company->shouldReceive('getId')->withNoArgs()->once()->andReturn($companyId = 234);

        $hubspot = Mockery::mock(Hubspot::class);
        $hubspot->shouldReceive('createContact')->with($user)->once()->andReturn($contact);
        $hubspot->shouldReceive('createCompany')->with($user, $hubspotId)->once()->andReturn($company);
        $hubspot->shouldReceive('associateCompanyContact')->with($companyId, $hubspotId)->once();

        App::bind(Hubspot::class, fn() => $hubspot);

        $job = new CreateUser($user);
        $job->handle();
    }

    /** @test */
    public function it_creates_nothing_if_there_hubspot_returns_no_id()
    {
        $user = Mockery::mock(User::class);

        $contact = Mockery::mock(Contact::class);
        $contact->shouldReceive('getId')->withNoArgs()->once()->andReturnFalse();

        $hubspot = Mockery::mock(Hubspot::class);
        $hubspot->shouldReceive('createContact')->with($user)->once()->andReturn($contact);

        App::bind(Hubspot::class, fn() => $hubspot);

        $job = new CreateUser($user);
        $job->handle();

        $user->shouldNotHaveReceived('setAttribute');
    }

    /** @test */
    public function it_creates_nothing_if_there_are_hubspot_issues()
    {
        $user = Mockery::mock(User::class);
        $user->shouldNotReceive('setAttribute');

        $hubspot = Mockery::mock(Hubspot::class);
        $hubspot->shouldReceive('createContact')->with($user)->once()->andReturnNull();

        App::bind(Hubspot::class, fn() => $hubspot);

        $job = new CreateUser($user);
        $job->handle();
    }

    /** @test */
    public function it_does_not_associate_company_when_creation_company_failed()
    {
        $user = Mockery::mock(User::class);

        $user->shouldReceive('setAttribute')->withArgs(['hubspot_id', $hubspotId = 123])->once();
        $user->shouldReceive('save')->withNoArgs()->once();

        $contact = Mockery::mock(Contact::class);
        $contact->shouldReceive('getId')->withNoArgs()->times(3)->andReturn($hubspotId);

        $hubspot = Mockery::mock(Hubspot::class);
        $hubspot->shouldReceive('createContact')->with($user)->once()->andReturn($contact);
        $hubspot->shouldReceive('createCompany')->with($user, $hubspotId)->once()->andReturnNull();
        $hubspot->shouldNotReceive('associateCompanyContact');

        App::bind(Hubspot::class, fn() => $hubspot);

        $job = new CreateUser($user);

        $job->handle();
    }

    /** @test */
    public function it_throws_an_exception_when_hubspot_failed()
    {
        $user = Mockery::mock(User::class);

        $user->shouldReceive('setAttribute')->withArgs(['hubspot_id', $hubspotId = 123])->once();
        $user->shouldReceive('save')->withNoArgs()->once();

        $contact = Mockery::mock(Contact::class);
        $contact->shouldReceive('getId')->withNoArgs()->times(3)->andReturn($hubspotId);

        $hubspot = Mockery::mock(Hubspot::class);
        $hubspot->shouldReceive('createContact')->with($user)->once()->andReturn($contact);
        $hubspot->shouldReceive('createCompany')->with($user, $hubspotId)->once()->andThrow(Exception::class);

        App::bind(Hubspot::class, fn() => $hubspot);

        $job = new CreateUser($user);

        $this->expectException(Exception::class);
        $job->handle();
    }
}
