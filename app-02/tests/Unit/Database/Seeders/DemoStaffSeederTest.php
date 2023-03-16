<?php

namespace Tests\Unit\Database\Seeders;

use App\Constants\Environments;
use App\Models\Staff;
use App\Models\Supplier;
use App\Models\SupplierHour;
use Arr;
use Database\Seeders\DemoStaffSeeder;
use Database\Seeders\DevelopmentUsersSeeder;
use Database\Seeders\EnvironmentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use ReflectionClass;
use Tests\TestCase;

class DemoStaffSeederTest extends TestCase
{
    use RefreshDatabase;

    const DEMO_EMAILS = [
        // Bluon
        'rscarff@bluon.com',
        'rscarff.2013@gmail.com',
        'rscarff@berkeley.edu',
        'dbunnett@bluon.com',
        'pcapuciati@bluon.com',
        'cdillon@bluon.com',
        'ipatel@bluon.com',
        'jmorrell@bluon.com',
        'jwilliams@bluon.com',
        'hgarcia@bluon.com',
        'acurry@bluon.com',
        'kgivens@bluon.com',
        // Admin
        'gabriel.zanetti@devbase.us',
        'matias.velilla@devbase.us',
        'emilio.bottino@devbase.us',
        'joaquin.steffan@devbase.us',
        'jorge.moreno@devbase.us',
        // BE
        'alejandro.rohmer@devbase.us',
        'carlos.rojas@devbase.us',
        'robert.lopez@devbase.us',
        'samir.fragozo@devbase.us',
        'fernando.ortuno@devbase.us',
        'facundo.condal@devbase.us',
        'damian.dicostanzo@devbase.us',
        'diego.romero@devbase.us',
        // FE
        'fernando.keim@devbase.us',
        'josue.angarita@devbase.us',
        'matias.nasiff@devbase.us',
        'roni.castro@devbase.us',
        'thiago.corta@devbase.us',
        'marcos.godoy@devbase.us',
        'alexis.borges@devbase.us',
        'reubert.barbosa@devbase.us',
        'dev@example.com',
        // QA
        'constanza.giorgetti@devbase.us',
        'emilia.bejarano@devbase.us',
        'kevin.di.julio@devbase.us',
        'walter.nolasco@devbase.us',
        'lucas.vazquez@devbase.us',
        'carlos.viniales@devbase.us',
        'estanislao.larriera@devbase.us',
        'tomas.ricolfi@devbase.us',
        'qabluon@gmail.com',
    ];

    /** @test */
    public function it_implements_interface()
    {
        $reflection = new ReflectionClass(DemoStaffSeeder::class);

        $this->assertTrue($reflection->implementsInterface(EnvironmentSeeder::class));
    }

    /** @test */
    public function it_uses_specific_demo_emails()
    {
        $this->assertEqualsCanonicalizing(self::DEMO_EMAILS, DemoStaffSeeder::DEMO_EMAILS);
    }

    /** @test */
    public function it_stores_all_demo_suppliers_and_staff()
    {
        $seeder = new DemoStaffSeeder();
        $seeder->run();

        foreach (DemoStaffSeeder::DEMO_EMAILS as $demoEmail) {
            $this->assertDatabaseHas(Supplier::tableName(), ['email' => $demoEmail]);
            $this->assertDatabaseHas(Staff::tableName(), ['email' => $demoEmail]);
        }
    }

    /** @test */
    public function it_creates_default_hours_for_each_supplier()
    {
        $seeder = new DemoStaffSeeder();
        $seeder->run();

        $count      = count(DemoStaffSeeder::DEMO_EMAILS);
        $from       = '9:00 am';
        $weekDayTo  = '5:00 pm';
        $saturdayTo = '1:00 pm';
        $days       = Collection::make([
            SupplierHour::DAY_MONDAY,
            SupplierHour::DAY_TUESDAY,
            SupplierHour::DAY_WEDNESDAY,
            SupplierHour::DAY_THURSDAY,
            SupplierHour::DAY_FRIDAY,
        ]);

        $this->assertDatabaseCount(SupplierHour::tableName(), 6 * $count);

        $days->each(function(string $day) use ($count, $seeder, $from, $weekDayTo) {
            $this->assertEquals($count,
                SupplierHour::where('day', $day)->where('from', $from)->where('to', $weekDayTo)->count());
        });

        $this->assertEquals($count, SupplierHour::where('day', SupplierHour::DAY_SATURDAY)
            ->where('from', $from)
            ->where('to', $saturdayTo)
            ->count());
    }

    /** @test */
    public function it_skips_creation_if_supplier_exists()
    {
        $demoEmail = Arr::first(self::DEMO_EMAILS);
        Supplier::factory()->createQuietly(['email' => $demoEmail]);

        $seeder = new DemoStaffSeeder();
        $seeder->run();

        $this->assertDatabaseHas(Supplier::tableName(), ['email' => $demoEmail]);
        $this->assertDatabaseMissing(Staff::tableName(), ['email' => $demoEmail]);
    }

    /** @test */
    public function it_skips_creation_if_staff_exists()
    {
        $demoEmail = Arr::first(self::DEMO_EMAILS);
        Staff::factory()->createQuietly(['email' => $demoEmail]);

        $seeder = new DemoStaffSeeder();
        $seeder->run();

        $this->assertDatabaseMissing(Supplier::tableName(), ['email' => $demoEmail]);
        $this->assertDatabaseHas(Staff::tableName(), ['email' => $demoEmail]);
    }

    /** @test */
    public function it_runs_in_specific_environments()
    {
        $seeder   = new DevelopmentUsersSeeder();
        $expected = [
            Environments::LOCAL,
            Environments::DEVELOPMENT,
            Environments::QA,
            Environments::QA2,
            Environments::DEMO,
            Environments::STAGING,
            Environments::UAT,
        ];

        $this->assertEquals($expected, $seeder->environments());
    }
}
