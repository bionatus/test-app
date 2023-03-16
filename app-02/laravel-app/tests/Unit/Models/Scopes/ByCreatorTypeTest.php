<?php

namespace Tests\Unit\Models\Scopes;

use App\Models\CustomItem;
use App\Models\Item;
use App\Models\User;
use App\Models\Scopes\ByCreatorType;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByCreatorTypeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_by_creator_type_on_custom_item()
    {
        $supplier = Supplier::factory()->createQuietly();
        CustomItem::factory()->count(3)->usingSupplier($supplier)->create(['creator_type' => Supplier::MORPH_ALIAS]);

        $expectedCustomItems = CustomItem::factory()->count(2)->create();

        $filtered = CustomItem::query()->scoped(new ByCreatorType(User::MORPH_ALIAS))->get();

        $this->assertEqualsCanonicalizing($expectedCustomItems->modelKeys(), $filtered->modelKeys());
    }
}
