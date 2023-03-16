<?php

namespace App\Http\Middleware;

use App\Constants\RelationsMorphs;
use Closure;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;

class SetRelationsMorphMap
{
    public function handle(Request $request, Closure $next)
    {
        Relation::morphMap(RelationsMorphs::MAP);

        return $next($request);
    }
}
