<?php

namespace Tests\Unit\Console\Commands\Sync;

use App\Console\Commands\Sync\AirtableSuppliersCommand;
use App\Jobs\Airtable\SyncSuppliers;
use Bus;
use ReflectionClass;
use Tests\TestCase;

class AirtableSuppliersCommandTest extends TestCase
{
    /** @test */
    public function it_dispatch_sync_suppliers_job()
    {
        Bus::fake([SyncSuppliers::class]);

        $this->artisan('sync:suppliers')->assertSuccessful();

        Bus::assertDispatched(SyncSuppliers::class);
    }

    /** @test */
    public function it_has_update_coordinates_option()
    {
        $command = new AirtableSuppliersCommand();

        $this->assertTrue($command->getDefinition()->hasOption('update-coordinates'));
        $this->assertEquals('U', $command->getDefinition()->getOption('update-coordinates')->getShortcut());
    }

    /** @test
     * @dataProvider dataProvider
     */
    public function it_send_update_coordinates_parameter_to_sync_stores_job(
        string $command,
        bool $expectedUpdateCoordinates
    ) {
        Bus::fake([SyncSuppliers::class]);

        $this->artisan($command)->assertSuccessful();

        Bus::assertDispatched(SyncSuppliers::class,
            function(SyncSuppliers $syncSuppliers) use ($expectedUpdateCoordinates) {
                $reflection = new ReflectionClass($syncSuppliers);
                $property   = $reflection->getProperty('updateCoordinates');
                $property->setAccessible(true);

                return $expectedUpdateCoordinates === $property->getValue($syncSuppliers);
            });
    }

    public function dataProvider(): array
    {
        return [
            ['sync:suppliers --update-coordinates', true],
            ['sync:suppliers', false],
        ];
    }
}
