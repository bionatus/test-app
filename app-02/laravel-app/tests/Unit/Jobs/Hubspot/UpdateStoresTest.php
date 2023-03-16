<?php

namespace Tests\Unit\Jobs\Hubspot;

use App\Jobs\Hubspot\UpdateStores;
use App\Models\User;
use App\Services\Hubspot\Hubspot;
use Exception;
use HubSpot\Client\Crm\Contacts\ApiException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

class UpdateStoresTest extends TestCase
{
    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(UpdateStores::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_uses_database_connection()
    {
        $job = new UpdateStores(new User());

        $this->assertEquals('database', $job->connection);
    }

    /** @test
     * @throws ApiException
     */
    public function it_updates_a_contact_in_hubspot_and_sets_hubspot_id_in_suppliers()
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->withArgs(['suppliers'])->once()->andReturn($suppliers = new Collection());

        $hubspot = Mockery::mock(Hubspot::class);
        $hubspot->shouldReceive('updateUserSuppliers')->withArgs([$user, $suppliers])->once();

        $job = new UpdateStores($user);
        $job->handle($hubspot);
    }

    /** @test
     * @throws ApiException
     */
    public function it_throws_an_exception_when_hubspot_failed()
    {
        $user = Mockery::mock(User::class);

        $user->shouldReceive('getAttribute')->withArgs(['suppliers'])->once()->andReturn($suppliers = new Collection());

        $hubspot = Mockery::mock(Hubspot::class);
        $hubspot->shouldReceive('updateUserSuppliers')->withArgs([$user, $suppliers])->once()->andThrow(Exception::class);

        $job = new UpdateStores($user);

        $this->expectException(Exception::class);
        $job->handle($hubspot);
    }
}
