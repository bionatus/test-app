<?php

namespace Tests\Feature\Api\V2;

use App\Http\Middleware\AcceptsJSON;
use App\Http\Middleware\ProvideLatamUser;
use App\Http\Middleware\SetRelationsMorphMap;
use JMac\Testing\Traits\AdditionalAssertions;

trait WithLatamMiddlewares
{
    use AdditionalAssertions;

    /** @test */
    public function it_uses_latam_guard()
    {
        $this->assertRouteUsesMiddleware($this->routeName, [ProvideLatamUser::class]);
    }

    /** @test */
    public function it_sets_relation_morph_maps()
    {
        $this->assertRouteUsesMiddleware($this->routeName, [SetRelationsMorphMap::class]);
    }

    /** @test */
    public function it_accepts_json()
    {
        $this->assertRouteUsesMiddleware($this->routeName, [AcceptsJSON::class]);
    }
}

