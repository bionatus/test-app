<?php

namespace Tests\Unit\Jobs\Hubspot;

use App;
use App\Jobs\Hubspot\UpdateUser;
use App\Models\User;
use App\Services\Hubspot\Hubspot;
use Exception;
use HubSpot\Client\Crm\Contacts\ApiException;
use HubSpot\Client\Crm\Contacts\Model\SimplePublicObjectInput as Contact;
use Illuminate\Contracts\Queue\ShouldQueue;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

class UpdateUserTest extends TestCase
{
    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(UpdateUser::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_uses_database_connection()
    {
        $job = new UpdateUser(new User());

        $this->assertEquals('database', $job->connection);
    }

    /** @test
     * @throws ApiException
     */
    public function it_updates_a_contact_in_hubspot_and_sets_hubspot_id_in_user()
    {
        $hubspotId = 123;

        $user = Mockery::mock(User::class);
        $user->shouldReceive('setAttribute')->withArgs(['hubspot_id', $hubspotId])->once();
        $user->shouldReceive('saveQuietly')->withNoArgs()->once();

        $contact = Mockery::mock(Contact::class);
        $contact->shouldReceive('getId')->withNoArgs()->times(2)->andReturn($hubspotId);

        $hubspot = Mockery::mock(Hubspot::class);
        $hubspot->shouldReceive('upsertContact')->with($user)->once()->andReturn($contact);

        App::bind(Hubspot::class, fn() => $hubspot);

        $job = new UpdateUser($user);
        $job->handle($hubspot);
    }

    /** @test
     * @throws ApiException
     */
    public function it_throws_an_exception_when_hubspot_failed()
    {
        $user = Mockery::mock(User::class);

        $hubspot = Mockery::mock(Hubspot::class);
        $hubspot->shouldReceive('upsertContact')->with($user)->once()->andThrow(Exception::class);

        App::bind(Hubspot::class, fn() => $hubspot);

        $job = new UpdateUser($user);

        $this->expectException(Exception::class);
        $job->handle($hubspot);
    }
}
