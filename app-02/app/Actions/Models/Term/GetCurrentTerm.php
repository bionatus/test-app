<?php

namespace App\Actions\Models\Term;

use App\Models\Scopes\Latest;
use App\Models\Term;
use App\Models\Term\Scopes\ByRequiredAtRange;
use App\Models\Term\Scopes\NewestRequiredAt;
use Carbon\Carbon;

class GetCurrentTerm
{
    public function execute(): ?Term
    {
        return Term::scoped(new ByRequiredAtRange(Carbon::now()))
            ->scoped(new NewestRequiredAt())
            ->scoped(new Latest())
            ->first();
    }
}
