<?php

namespace Database\Seeders;

use App\Constants\Environments;
use App\Constants\Timezones;
use App\Models\StateTimezone;
use Arr;
use Illuminate\Database\Seeder;

class StateTimezonesSeeder extends Seeder implements EnvironmentSeeder
{
    use SeedsEnvironment;

    const STATE_TIMEZONES = [
        [
            'country'  => 'US',
            'state'    => 'US-AK',
            'timezone' => Timezones::AMERICA_ANCHORAGE,
        ],
        [
            'country'  => 'US',
            'state'    => 'US-AL',
            'timezone' => Timezones::AMERICA_CHICAGO,
        ],
        [
            'country'  => 'US',
            'state'    => 'US-AR',
            'timezone' => Timezones::AMERICA_CHICAGO,
        ],
        [
            'country'  => 'US',
            'state'    => 'US-AZ',
            'timezone' => Timezones::AMERICA_PHOENIX,
        ],
        [
            'country'  => 'US',
            'state'    => 'US-CA',
            'timezone' => Timezones::AMERICA_LOS_ANGELES,
        ],
        [
            'country'  => 'US',
            'state'    => 'US-CO',
            'timezone' => Timezones::AMERICA_DENVER,
        ],
        [
            'country'  => 'US',
            'state'    => 'US-CT',
            'timezone' => Timezones::AMERICA_NEW_YORK,
        ],
        [
            'country'  => 'US',
            'state'    => 'US-DC',
            'timezone' => Timezones::AMERICA_NEW_YORK,
        ],
        [
            'country'  => 'US',
            'state'    => 'US-DE',
            'timezone' => Timezones::AMERICA_NEW_YORK,
        ],
        [
            'country'  => 'US',
            'state'    => 'US-GA',
            'timezone' => Timezones::AMERICA_NEW_YORK,
        ],
        [
            'country'  => 'US',
            'state'    => 'US-GU',
            'timezone' => Timezones::PACIFIC_GUAM,
        ],
        [
            'country'  => 'US',
            'state'    => 'US-HI',
            'timezone' => Timezones::AMERICA_ADAK,
        ],
        [
            'country'  => 'US',
            'state'    => 'US-IA',
            'timezone' => Timezones::AMERICA_CHICAGO,
        ],
        [
            'country'  => 'US',
            'state'    => 'US-IL',
            'timezone' => Timezones::AMERICA_CHICAGO,
        ],
        [
            'country'  => 'US',
            'state'    => 'US-LA',
            'timezone' => Timezones::AMERICA_CHICAGO,
        ],
        [
            'country'  => 'US',
            'state'    => 'US-MA',
            'timezone' => Timezones::AMERICA_NEW_YORK,
        ],
        [
            'country'  => 'US',
            'state'    => 'US-MD',
            'timezone' => Timezones::AMERICA_NEW_YORK,
        ],
        [
            'country'  => 'US',
            'state'    => 'US-ME',
            'timezone' => Timezones::AMERICA_NEW_YORK,
        ],
        [
            'country'  => 'US',
            'state'    => 'US-MI',
            'timezone' => Timezones::AMERICA_NEW_YORK,
        ],
        [
            'country'  => 'US',
            'state'    => 'US-MN',
            'timezone' => Timezones::AMERICA_CHICAGO,
        ],
        [
            'country'  => 'US',
            'state'    => 'US-MO',
            'timezone' => Timezones::AMERICA_CHICAGO,
        ],
        [
            'country'  => 'US',
            'state'    => 'US-MS',
            'timezone' => Timezones::AMERICA_CHICAGO,
        ],
        [
            'country'  => 'US',
            'state'    => 'US-MT',
            'timezone' => Timezones::AMERICA_DENVER,
        ],
        [
            'country'  => 'US',
            'state'    => 'US-NC',
            'timezone' => Timezones::AMERICA_NEW_YORK,
        ],
        [
            'country'  => 'US',
            'state'    => 'US-NH',
            'timezone' => Timezones::AMERICA_NEW_YORK,
        ],
        [
            'country'  => 'US',
            'state'    => 'US-NJ',
            'timezone' => Timezones::AMERICA_NEW_YORK,
        ],
        [
            'country'  => 'US',
            'state'    => 'US-NM',
            'timezone' => Timezones::AMERICA_DENVER,
        ],
        [
            'country'  => 'US',
            'state'    => 'US-NV',
            'timezone' => Timezones::AMERICA_LOS_ANGELES,
        ],
        [
            'country'  => 'US',
            'state'    => 'US-NY',
            'timezone' => Timezones::AMERICA_NEW_YORK,
        ],
        [
            'country'  => 'US',
            'state'    => 'US-OH',
            'timezone' => Timezones::AMERICA_NEW_YORK,
        ],
        [
            'country'  => 'US',
            'state'    => 'US-OK',
            'timezone' => Timezones::AMERICA_CHICAGO,
        ],
        [
            'country'  => 'US',
            'state'    => 'US-PA',
            'timezone' => Timezones::AMERICA_NEW_YORK,
        ],
        [
            'country'  => 'US',
            'state'    => 'US-RI',
            'timezone' => Timezones::AMERICA_NEW_YORK,
        ],
        [
            'country'  => 'US',
            'state'    => 'US-SC',
            'timezone' => Timezones::AMERICA_NEW_YORK,
        ],
        [
            'country'  => 'US',
            'state'    => 'US-UT',
            'timezone' => Timezones::AMERICA_DENVER,
        ],
        [
            'country'  => 'US',
            'state'    => 'US-VA',
            'timezone' => Timezones::AMERICA_NEW_YORK,
        ],
        [
            'country'  => 'US',
            'state'    => 'US-VT',
            'timezone' => Timezones::AMERICA_NEW_YORK,
        ],
        [
            'country'  => 'US',
            'state'    => 'US-WA',
            'timezone' => Timezones::AMERICA_LOS_ANGELES,
        ],
        [
            'country'  => 'US',
            'state'    => 'US-WI',
            'timezone' => Timezones::AMERICA_CHICAGO,
        ],
        [
            'country'  => 'US',
            'state'    => 'US-WV',
            'timezone' => Timezones::AMERICA_NEW_YORK,
        ],
        [
            'country'  => 'US',
            'state'    => 'US-WY',
            'timezone' => Timezones::AMERICA_DENVER,
        ],
    ];

    public function run()
    {
        foreach (self::STATE_TIMEZONES as $stateTimezone) {
            $attributes = Arr::except($stateTimezone, ['timezone']);

            StateTimezone::updateOrCreate($attributes, $stateTimezone);
        }
    }

    public function environments(): array
    {
        return Environments::ALL_BUT_TESTING;
    }
}
