<?php

namespace Database\Seeders;

use App\Models\Staff;
use App\Models\Supplier;
use App\Models\Supplier\Scopes\ByEmail;
use App\Models\SupplierHour;
use Config;
use Hash;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class StubSuppliersSeeder extends Seeder
{
    private Collection $days;

    public function __construct()
    {
        $this->days = Collection::make([
            SupplierHour::DAY_MONDAY,
            SupplierHour::DAY_TUESDAY,
            SupplierHour::DAY_WEDNESDAY,
            SupplierHour::DAY_THURSDAY,
            SupplierHour::DAY_FRIDAY,
        ]);
    }

    public function run()
    {
        $password = Hash::make(Config::get('live.staff.default_password'));
        $count    = Config::get('live.staff.stub_seed_count');

        for ($index = 1; $index <= $count; $index += 1) {
            $email = "bluon$index@bluon.com";

            if (Supplier::scoped(new ByEmail($email))->limit(1)->count()) {
                continue;
            }

            $supplier = Supplier::factory()->createQuietly(['name' => "Bluon $index", 'email' => $email]);

            $supplier->staff()->create([
                'type'     => Staff::TYPE_OWNER,
                'email'    => $supplier->email,
                'password' => $password,
            ]);

            $this->createSupplierHours($supplier);
        }
    }

    private function createSupplierHours(Supplier $supplier)
    {
        $this->days->each(function(string $day) use ($supplier) {
            $supplier->supplierHours()->create([
                'day'  => $day,
                'from' => SupplierHour::DEFAULT_WEEK_DAY_FROM,
                'to'   => SupplierHour::DEFAULT_WEEK_DAY_TO,
            ]);
        });
        $supplier->supplierHours()->create([
            'day'  => SupplierHour::DAY_SATURDAY,
            'from' => SupplierHour::DEFAULT_WEEK_DAY_FROM,
            'to'   => SupplierHour::DEFAULT_SATURDAY_TO,
        ]);
    }
}
