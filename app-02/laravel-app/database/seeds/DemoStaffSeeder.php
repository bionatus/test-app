<?php

namespace Database\Seeders;

use App\Constants\Environments;
use App\Models\Staff;
use App\Models\Supplier;
use App\Models\SupplierHour;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class DemoStaffSeeder extends Seeder implements EnvironmentSeeder
{
    use SeedsEnvironment;

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
        'diego.romero@devbase.us',
        'robert.lopez@devbase.us',
        'samir.fragozo@devbase.us',
        'fernando.ortuno@devbase.us',
        'facundo.condal@devbase.us',
        'damian.dicostanzo@devbase.us',
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

    public function run()
    {
        foreach (self::DEMO_EMAILS as $demoEmail) {
            if (Supplier::firstWhere('email', $demoEmail) || Staff::firstWhere('email', $demoEmail)) {
                continue;
            }

            $supplier = Supplier::factory()->createQuietly(['email' => $demoEmail]);

            $supplier->staff()->create([
                'type'     => Staff::TYPE_OWNER,
                'email'    => $supplier->email,
                'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            ]);

            $from       = '9:00 am';
            $weekDayTo  = '5:00 pm';
            $saturdayTo = '1:00 pm';

            $days = Collection::make([
                SupplierHour::DAY_MONDAY,
                SupplierHour::DAY_TUESDAY,
                SupplierHour::DAY_WEDNESDAY,
                SupplierHour::DAY_THURSDAY,
                SupplierHour::DAY_FRIDAY,
            ]);

            $days->each(function(string $day) use ($supplier, $from, $weekDayTo) {
                $supplier->supplierHours()->create([
                    'day'  => $day,
                    'from' => $from,
                    'to'   => $weekDayTo,
                ]);
            });

            $supplier->supplierHours()->create([
                'day'  => SupplierHour::DAY_SATURDAY,
                'from' => $from,
                'to'   => $saturdayTo,
            ]);
        }
    }

    public function environments(): array
    {
        return Environments::ALL_BUT_PRODUCTION_OR_TESTING;
    }
}
