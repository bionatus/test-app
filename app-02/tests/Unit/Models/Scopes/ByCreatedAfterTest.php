<?php

namespace Tests\Unit\Models\Scopes;

use App\Models\AuthenticationCode;
use App\Models\Scopes\ByCreatedAfter;
use App\Models\Status;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByCreatedAfterTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_by_creation_date_on_status_model()
    {
        $date = CarbonImmutable::now();
        Status::factory()->count(10)->createQuietly(['created_at' => $date]);
        $expected = Status::factory()->count(3)->createQuietly(['created_at' => $date->addDays(10)]);

        $statuses = Status::scoped(new ByCreatedAfter($date->addDays(5)))->get();

        $this->assertCount(3, $statuses);
        $statuses->each(function(Status $status) use ($expected) {
            $this->assertSame($expected->shift()->getKey(), $status->getKey());
        });
    }

    /** @test */
    public function it_filters_by_creation_date_on_authentication_code_model()
    {
        $date = CarbonImmutable::now();
        AuthenticationCode::factory()->count(2)->create(['created_at' => $date]);
        AuthenticationCode::factory()->count(3)->create(['created_at' => $date->addSeconds(10)]);

        $authenticationCodes = AuthenticationCode::scoped(new ByCreatedAfter($date->addSeconds(5)))->get();

        $this->assertCount(3, $authenticationCodes);
    }
}
