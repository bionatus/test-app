<?php

namespace Database\Seeders;

use App\Constants\Environments;
use App\Models\Term;
use Illuminate\Database\Seeder;

class TermsSeeder extends Seeder implements EnvironmentSeeder
{
    use SeedsEnvironment;

    const TERM = [
        'title'       => 'Initial Term',
        'body'        => 'Initial Body term',
        'link'        => 'https://www.bluon.com/',
        'required_at' => '2022-09-01',
    ];

    public function run()
    {
        if (!Term::query()->count()) {
            Term::updateOrCreate(self::TERM);
        }
    }

    public function environments(): array
    {
        return Environments::ALL_BUT_TESTING;
    }
}
