<?php

namespace Database\Seeders;

use App\Constants\Environments;
use App\Layout;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LayoutsSeeder extends Seeder implements EnvironmentSeeder
{
    use SeedsEnvironment;

    public function run()
    {
        $files = [
            'layout-2.5.json',
            'layout-3.0.json',
            'layout-4.0.json',
            'layout-4.3.json',
            'layout-4.4.json',
            'layout-5.0.json',
            'layout-6.0.json',
            'layout-7.0.json',
            'layout-8.0.json',
        ];

        DB::table('layouts')->truncate();

        foreach ($files as $filename) {
            Layout::create($this->readJSON($filename));
        }
    }

    protected function readJSON(string $filename): array
    {
        $contents = file_get_contents(database_path('data/' . $filename));

        return json_decode($contents, true);
    }

    public function environments(): array
    {
        return Environments::ALL_BUT_TESTING;
    }
}
