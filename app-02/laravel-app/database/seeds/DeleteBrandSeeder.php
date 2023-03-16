<?php

namespace Database\Seeders;

use App\Constants\Environments;
use App\Models\Brand;
use App\Models\Tag;
use App\Models\Tag\Scopes\ByTaggableType;
use App\Models\UserTaggable;
use Exception;
use Illuminate\Database\Seeder;

class DeleteBrandSeeder extends Seeder implements EnvironmentSeeder
{
    use SeedsEnvironment;

    public function environments(): array
    {
        return Environments::ALL_BUT_TESTING;
    }

    /**
     * @throws Exception
     */
    public function run()
    {
        $brandUserTaggables = UserTaggable::scoped(new ByTaggableType(Brand::MORPH_ALIAS));
        $brandUserTaggables->delete();

        $brandTags = Tag::scoped(new ByTaggableType(Brand::MORPH_ALIAS));
        $brandTags->delete();
    }
}
