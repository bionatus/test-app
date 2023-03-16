<?php

namespace Database\Seeders;

use App\Constants\Environments;
use App\Models\NoteCategory;
use Illuminate\Database\Seeder;

class NoteCategoriesSeeder extends Seeder implements EnvironmentSeeder
{
    use SeedsEnvironment;

    const NOTE_CATEGORIES = [
        NoteCategory::SLUG_GAMIFICATION => [
            'name' => 'Gamification',
        ],
        NoteCategory::SLUG_FEATURED     => [
            'name' => 'Featured',
        ],
    ];

    public function run()
    {
        foreach (self::NOTE_CATEGORIES as $slug => $category) {
            NoteCategory::updateOrCreate([NoteCategory::routeKeyName() => $slug], $category);
        }
    }

    public function environments(): array
    {
        return Environments::ALL_BUT_TESTING;
    }
}
