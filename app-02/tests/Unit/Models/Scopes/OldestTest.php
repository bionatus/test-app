<?php

namespace Tests\Unit\Models\Scopes;

use App\Models\Phone;
use App\Models\Scopes\Oldest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class OldestTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_orders_by_oldest_creation_and_id_on_phone_model()
    {
        $now = Carbon::now();
        $phones = Phone::factory()->unverified()->count(3)->sequence(fn($sequence) => [
            'created_at' => $now->subSeconds($sequence->index),
        ])->create();
        $phonesWithSameCreationDate = Phone::factory()->unverified()->count(3)->create();

        $oldest = $phones->reverse()->merge($phonesWithSameCreationDate)->values();

        $this->assertEquals($oldest->pluck('id'), Phone::scoped(new Oldest())->pluck('id'));
    }
}
