<?php

namespace Tests\Feature\Nova\Resources;

use App;
use App\Models\AppSetting;
use App\Models\Level;
use App\Models\Point;
use App\Models\User;
use App\User as NovaUser;
use Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\Nova\TestCase;

/** @see \App\Nova\Resources\Point */
class PointTest extends TestCase
{
    use RefreshDatabase;

    private string $path;

    protected function setUp(): void
    {
        parent::setUp();
        $this->path = '/nova-api/' . App\Nova\Resources\Point::uriKey() . DIRECTORY_SEPARATOR;
    }

    /** @test */
    public function it_displays_a_list_of_points()
    {
        $user   = User::factory()->create();
        $points = Point::factory()->usingUser($user)->count(40)->createQuietly();

        $response = $this->getJson($this->path);
        $response->assertStatus(Response::HTTP_OK);

        $this->assertCount($response->json('total'), $points);

        $data = Collection::make($response->json('resources'));

        $firstPageStores = $points->sortByDesc('id')->values()->take(count($data));

        $this->assertEquals($data->pluck('id.value'), $firstPageStores->pluck('id'),
            'Resources are not in the correct order.');
    }

    /** @test */
    public function it_creates_a_point()
    {
        Level::factory()->create([
            'slug'        => 'level-0',
            'from'        => 0,
            'to'          => 999,
            'coefficient' => 0.5,
        ]);

        AppSetting::factory()->create([
            'slug'  => AppSetting::SLUG_BLUON_POINTS_MULTIPLIER,
            'type'  => AppSetting::TYPE_INTEGER,
            'value' => 2,
        ]);

        $user     = User::factory()->create();
        $response = $this->postJson($this->path, [
            'points_earned'   => $pointsEarned = 7,
            'viaResource'     => 'latam-' . $user->tableName(),
            'viaResourceId'   => $user->getKey(),
            'viaRelationship' => 'points',
        ]);

        $response->assertJsonMissingValidationErrors();
        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseHas(Point::tableName(), [
            'object_type'   => NovaUser::class,
            'object_id'     => Auth::user()->getKey(),
            'action'        => Point::ACTION_ADJUSTMENT,
            'points_earned' => $pointsEarned,
            'multiplier'    => 1,
            'coefficient'   => 1,
        ]);
    }
}
