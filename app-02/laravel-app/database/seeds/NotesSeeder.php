<?php

namespace Database\Seeders;

use App\Constants\Environments;
use App\Models\Note;
use App\Models\NoteCategory;
use App\Models\Scopes\ByRouteKey;
use Illuminate\Database\Seeder;

class NotesSeeder extends Seeder implements EnvironmentSeeder
{
    use SeedsEnvironment;

    const GAMIFICATION_NOTE = [
        'title' => 'Get parts, get paid!',
        'slug'  => Note::SLUG_GAMIFICATION_NOTE,
        'body'  => 'Get $20 for every $1,000 you spend on parts + truck stock',
        'link'  => 'https://bluon.com/',
    ];

    public function run()
    {
        $gamificationNoteCategory = NoteCategory::scoped(new ByRouteKey(NoteCategory::SLUG_GAMIFICATION))->first();

        $gamificationNoteData                     = self::GAMIFICATION_NOTE;
        $gamificationNoteData['note_category_id'] = $gamificationNoteCategory->getKey();
        Note::updateOrCreate([Note::routeKeyName() => Note::SLUG_GAMIFICATION_NOTE], $gamificationNoteData);
    }

    public function environments(): array
    {
        return Environments::ALL_BUT_TESTING;
    }
}
