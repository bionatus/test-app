<?php

namespace Tests\Feature\LiveApi\V1\Order\ItemOrder\Replacement;

use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Http\Controllers\LiveApi\V1\Order\ItemOrder\ReplacementController;
use App\Http\Resources\LiveApi\V1\Order\ItemOrder\Replacement\BaseResource;
use App\Models\AirFilter;
use App\Models\GroupedReplacement;
use App\Models\Item;
use App\Models\ItemOrder;
use App\Models\Order;
use App\Models\Part;
use App\Models\Replacement;
use App\Models\ReplacementNote;
use App\Models\SingleReplacement;
use App\Models\Staff;
use App\Models\Supplier;
use Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use JMac\Testing\Traits\AdditionalAssertions;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see ReplacementController */
class IndexTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = RouteNames::LIVE_API_V1_ORDER_ITEM_ORDER_REPLACEMENT_INDEX;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $supplier  = Supplier::factory()->createQuietly();
        $order     = Order::factory()->usingSupplier($supplier)->create();
        $item      = Item::factory()->part()->create();
        $itemOrder = ItemOrder::factory()->usingOrder($order)->usingItem($item)->create();

        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->get(URL::route($this->routeName,
            [RouteParameters::ORDER => $order, RouteParameters::PART_ITEM_ORDER => $itemOrder]));
    }

    /** @test */
    public function it_displays_an_error_with_a_supply_part()
    {
        $supplier  = Supplier::factory()->createQuietly();
        $order     = Order::factory()->usingSupplier($supplier)->create();
        $item      = Item::factory()->create();
        $itemOrder = ItemOrder::factory()->usingOrder($order)->usingItem($item)->create();

        $this->withoutExceptionHandling();

        $this->expectException(ModelNotFoundException::class);

        $this->get(URL::route($this->routeName,
            [RouteParameters::ORDER => $order, RouteParameters::PART_ITEM_ORDER => $itemOrder]));
    }

    /** @test */
    public function it_uses_can_policy()
    {
        $this->assertRouteUsesMiddleware($this->routeName, ['can:read,' . RouteParameters::ORDER]);
    }

    /** @test */
    public function it_displays_a_replacement_list_for_a_part()
    {
        $staff = Staff::factory()->createQuietly();
        $order = Order::factory()->usingSupplier($staff->supplier)->create();
        $part  = Part::factory()->create();
        AirFilter::factory()->usingPart($part)->create();
        $itemOrder = ItemOrder::factory()->usingOrder($order)->usingItem($part->item)->create();

        $singleReplacementWithNote = Replacement::factory()->single()->usingPart($part)->create();
        ReplacementNote::factory()->usingReplacement($singleReplacementWithNote)->create();
        $groupedReplacement           = Replacement::factory()->grouped()->usingPart($part)->create();
        $singleReplacementWithoutNote = Replacement::factory()->single()->usingPart($part)->create();

        $replacementPart = Part::factory()->create([
            'brand' => 'fake brand',
        ]);
        SingleReplacement::factory()
            ->usingReplacement($singleReplacementWithNote)
            ->usingPart($replacementPart)
            ->create();
        GroupedReplacement::factory()->usingReplacement($groupedReplacement)->create();
        SingleReplacement::factory()
            ->usingReplacement($singleReplacementWithoutNote)
            ->usingPart($replacementPart)
            ->create();

        $route = URL::route($this->routeName,
            [RouteParameters::ORDER => $order, RouteParameters::PART_ITEM_ORDER => $itemOrder]);

        Auth::shouldUse('live');
        $this->login($staff);
        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), true);
        $this->validateResponseSchema($schema, $response);

        $this->assertCount(3, $response->json('data'));
    }

    /** @test */
    public function it_displays_replacements_with_notes_first()
    {
        $staff = Staff::factory()->createQuietly();
        $order = Order::factory()->usingSupplier($staff->supplier)->create();
        $part  = Part::factory()->create();
        AirFilter::factory()->usingPart($part)->create();
        $itemOrder = ItemOrder::factory()->usingOrder($order)->usingItem($part->item)->create();

        $replacementsWithNote = Replacement::factory()->single()->usingPart($part)->count(2)->create();
        $replacementsWithNote->each(function($replacement) {
            ReplacementNote::factory()->usingReplacement($replacement)->create(['value' => 'XX another note']);
        });

        Replacement::factory()->single()->usingPart($part)->count(3)->create();

        $replacementPart = Part::factory()->create([
            'brand' => 'fake brand',
        ]);

        Replacement::all()->each(function($replacement) use ($replacementPart) {
            SingleReplacement::factory()->usingReplacement($replacement)->usingPart($replacementPart)->create();
        });

        $route = URL::route($this->routeName,
            [RouteParameters::ORDER => $order, RouteParameters::PART_ITEM_ORDER => $itemOrder]);

        Auth::shouldUse('live');
        $this->login($staff);
        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), true);
        $this->validateResponseSchema($schema, $response);

        $data = Collection::make($response->json('data'));

        $data->take(2)->each(function(array $rawReplacement) {
            $this->assertNotNull($rawReplacement['note']);
        });

        $data->take(-3)->each(function(array $rawReplacement) {
            $this->assertNull($rawReplacement['note']);
        });
    }

    /** @test */
    public function it_displays_a_replacement_list_for_a_part_sorted_by_note_alphabetically()
    {
        $staff = Staff::factory()->createQuietly();
        $order = Order::factory()->usingSupplier($staff->supplier)->create();
        $part  = Part::factory()->create();
        AirFilter::factory()->usingPart($part)->create();
        $itemOrder = ItemOrder::factory()->usingOrder($order)->usingItem($part->item)->create();

        $firstReplacementWithNote = Replacement::factory()->single()->usingPart($part)->create();
        ReplacementNote::factory()->usingReplacement($firstReplacementWithNote)->create(['value' => 'zz another note']);

        $secondReplacementWithNote = Replacement::factory()->single()->usingPart($part)->create();
        ReplacementNote::factory()
            ->usingReplacement($secondReplacementWithNote)
            ->create(['value' => 'xx another note']);

        $thirdReplacementWithNote = Replacement::factory()->single()->usingPart($part)->create();
        ReplacementNote::factory()->usingReplacement($thirdReplacementWithNote)->create(['value' => 'yy another note']);

        $replacementPart = Part::factory()->create([
            'brand' => 'fake brand',
        ]);

        $expectedReplacements = Collection::make([
            $secondReplacementWithNote,
            $thirdReplacementWithNote,
            $firstReplacementWithNote,
        ]);

        Replacement::all()->each(function($replacement) use ($replacementPart) {
            SingleReplacement::factory()->usingReplacement($replacement)->usingPart($replacementPart)->create();
        });

        $route = URL::route($this->routeName,
            [RouteParameters::ORDER => $order, RouteParameters::PART_ITEM_ORDER => $itemOrder]);

        Auth::shouldUse('live');
        $this->login($staff);
        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), true);
        $this->validateResponseSchema($schema, $response);

        $data = Collection::make($response->json('data'));

        $data->each(function(array $rawReplacement, int $index) use ($expectedReplacements) {
            $replacement = $expectedReplacements->get($index);
            $this->assertSame($replacement->getRouteKey(), $rawReplacement['id']);
        });
    }

    /** @test */
    public function it_displays_a_replacement_list_for_a_part_sorted_by_note_alphabetically_then_by_brand_alphabetically(
    )
    {
        $staff = Staff::factory()->createQuietly();
        $order = Order::factory()->usingSupplier($staff->supplier)->create();
        $part  = Part::factory()->create();
        AirFilter::factory()->usingPart($part)->create();
        $itemOrder = ItemOrder::factory()->usingOrder($order)->usingItem($part->item)->create();

        $firstReplacementWithNote = Replacement::factory()->single()->usingPart($part)->create();
        ReplacementNote::factory()->usingReplacement($firstReplacementWithNote)->create(['value' => 'xx another note']);

        $secondReplacementWithNote = Replacement::factory()->single()->usingPart($part)->create();
        ReplacementNote::factory()
            ->usingReplacement($secondReplacementWithNote)
            ->create(['value' => 'xx another note']);

        $thirdReplacementWithNote = Replacement::factory()->single()->usingPart($part)->create();
        ReplacementNote::factory()->usingReplacement($thirdReplacementWithNote)->create(['value' => 'xx another note']);

        $withoutBrandReplacement = Replacement::factory()->single()->usingPart($part)->create();
        ReplacementNote::factory()->usingReplacement($withoutBrandReplacement)->create(['value' => 'xx another note']);

        $withoutBrand = Part::factory()->create(['brand' => null]);

        $firstReplacementPart = Part::factory()->create([
            'brand' => 'C fake brand',
        ]);

        $secondReplacementPart = Part::factory()->create([
            'brand' => 'A fake brand',
        ]);

        $thirdReplacementPart = Part::factory()->create([
            'brand' => 'B fake brand',
        ]);

        SingleReplacement::factory()->usingReplacement($withoutBrandReplacement)->usingPart($withoutBrand)->create();
        SingleReplacement::factory()
            ->usingReplacement($secondReplacementWithNote)
            ->usingPart($secondReplacementPart)
            ->create();
        SingleReplacement::factory()
            ->usingReplacement($thirdReplacementWithNote)
            ->usingPart($thirdReplacementPart)
            ->create();
        SingleReplacement::factory()
            ->usingReplacement($firstReplacementWithNote)
            ->usingPart($firstReplacementPart)
            ->create();

        $expectedReplacements = Collection::make([
            $secondReplacementWithNote,
            $thirdReplacementWithNote,
            $firstReplacementWithNote,
            $withoutBrandReplacement,
        ]);

        $route = URL::route($this->routeName,
            [RouteParameters::ORDER => $order, RouteParameters::PART_ITEM_ORDER => $itemOrder]);

        Auth::shouldUse('live');
        $this->login($staff);
        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), true);
        $this->validateResponseSchema($schema, $response);
        $this->assertCount($response->json('meta.total'), $expectedReplacements);

        $data = Collection::make($response->json('data'));

        $data->each(function(array $rawReplacement, int $index) use ($expectedReplacements) {
            $replacement = $expectedReplacements->get($index);
            $this->assertSame($replacement->getRouteKey(), $rawReplacement['id']);
        });
    }

    /** @test */
    public function it_sorts_replacements_with_same_note_value_by_single_type_and_alphabetically_and_by_key()
    {
        $staff = Staff::factory()->createQuietly();
        $order = Order::factory()->usingSupplier($staff->supplier)->create();
        $part  = Part::factory()->create();
        AirFilter::factory()->usingPart($part)->create();
        $itemOrder = ItemOrder::factory()->usingOrder($order)->usingItem($part->item)->create();

        $replacementWithNotes = Replacement::factory()->usingPart($part)->count(9)->create();
        $replacementNotes     = Collection::make([]);
        $replacementWithNotes->each(function(Replacement $replacement, int $index) use ($replacementNotes) {
            $replacementNotes->add(ReplacementNote::factory()
                ->usingReplacement($replacement)
                ->create(['value' => '0' . ($index + 1) . ' fake note']));
        });
        $sortedReplacements = Collection::make([]);
        $replacementNotes->each(function(ReplacementNote $replacementNote) use ($sortedReplacements) {
            $sortedReplacements->add($replacementNote->replacement);
        });

        $replacementsWithSameNote      = Replacement::factory()->usingPart($part)->count(10)->create();
        $replacementNotesWithSameValue = Collection::make([]);
        $replacementsWithSameNote->each(function(Replacement $replacement) use ($replacementNotesWithSameValue) {
            $replacementNotesWithSameValue->add(ReplacementNote::factory()
                ->usingReplacement($replacement)
                ->create(['value' => 'same fake note']));
        });
        $replacementNotesWithSameValue->each(function(ReplacementNote $replacementNote) use ($sortedReplacements) {
            $sortedReplacements->add($replacementNote->replacement);
        });

        $firstReplacement     = Replacement::factory()->usingPart($part)->create();
        $firstReplacementNote = ReplacementNote::factory()
            ->usingReplacement($firstReplacement)
            ->create(['value' => '00 fake note']);
        $sortedReplacements->prepend($firstReplacementNote->replacement);

        $replacementWithoutNotes = Replacement::factory()->usingPart($part)->count(20)->create();

        $expectedReplacements = $sortedReplacements->merge($replacementWithoutNotes);

        $partToReplace = Part::factory()->create(['brand' => 'fake brand']);
        $expectedReplacements->each(function(Replacement $replacement) use ($partToReplace) {
            SingleReplacement::factory()->usingPart($partToReplace)->usingReplacement($replacement)->create();
        });

        $route = URL::route($this->routeName,
            [RouteParameters::ORDER => $order, RouteParameters::PART_ITEM_ORDER => $itemOrder]);

        Auth::shouldUse('live');
        $this->login($staff);
        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), true);
        $this->validateResponseSchema($schema, $response);
        $this->assertCount($response->json('meta.total'), $expectedReplacements);

        $data = Collection::make($response->json('data'));

        $data->each(function(array $rawReplacement, int $index) use ($expectedReplacements) {
            $replacement = $expectedReplacements->get($index);
            $this->assertSame($replacement->getRouteKey(), $rawReplacement['id']);
        });
    }
}
