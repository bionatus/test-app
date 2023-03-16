<?php

namespace Tests\Unit\Database\Seeders;

use App\Models\Scopes\ByType;
use App\Models\Staff;
use App\Models\Supplier;
use App\Models\Supplier\Scopes\ByEmail;
use App\Models\SupplierHour;
use Config;
use Database\Seeders\StubSuppliersSeeder;
use Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class StubSuppliersSeederTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_creates_all_suppliers_and_staff_owners()
    {
        Config::set('live.staff.stub_seed_count', $count = 10);
        $seeder = new StubSuppliersSeeder();
        $seeder->run();

        $this->assertDatabaseCount(Supplier::tableName(), $count);
        $this->assertEquals($count, Staff::scoped(new ByType(Staff::TYPE_OWNER))->count());
    }

    /** @test */
    public function it_assign_an_email_with_incremental_number()
    {
        Config::set('live.staff.stub_seed_count', $count = 10);

        $seeder = new StubSuppliersSeeder();
        $seeder->run();

        $this->assertDatabaseCount(Supplier::tableName(), $count);
        $this->assertEquals($count, Supplier::where('email', 'LIKE', "bluon%@bluon.com")->count());
        $this->assertEquals(1, Supplier::scoped(new ByEmail('bluon1@bluon.com'))->count());
        $this->assertEquals(1, Supplier::scoped(new ByEmail('bluon10@bluon.com'))->count());

        $supplier = Supplier::first();
        $this->assertEquals('bluon1@bluon.com', $supplier->email);
        $staff = Staff::first();
        $this->assertEquals('bluon1@bluon.com', $staff->email);
    }

    /** @test */
    public function it_assign_a_default_password_for_each_staff_owner()
    {
        Config::set('live.staff.default_password', $password = 'a random password');
        Config::set('live.staff.stub_seed_count', 1);

        $seeder = new StubSuppliersSeeder();
        $seeder->run();

        $staff = Staff::first();
        $this->assertTrue(Hash::check($password, $staff->password));
    }

    /** @test */
    public function it_creates_default_hours_for_each_supplier()
    {
        Config::set('live.staff.stub_seed_count', $count = 2);

        $seeder = new StubSuppliersSeeder();
        $seeder->run();

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
    public function it_ignores_a_record_if_the_supplier_email_is_already_registered()
    {
        Config::set('live.staff.stub_seed_count', $count = 5);

        $supplier = Supplier::factory()->createQuietly(['email' => 'bluon3@bluon.com', 'name' => 'Existing Supplier']);
        Staff::factory()->usingSupplier($supplier)->create();

        $seeder = new StubSuppliersSeeder();
        $seeder->run();

        $this->assertDatabaseCount(Supplier::tableName(), $count);
        $this->assertEquals($count, Staff::scoped(new ByType(Staff::TYPE_OWNER))->count());

        $this->assertEquals('Existing Supplier', $supplier->fresh()->name);
    }
}
