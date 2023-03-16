<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\User\Scopes\WithNullPublicName;
use Illuminate\Foundation\Bus\Dispatchable;

class AssignPublicNameToUsers
{
    use Dispatchable;

    public function handle()
    {
        User::withoutEvents(function() {
            User::scoped(new WithNullPublicName())->cursor()->each(function(User $user) {
                $user->generateSlug();
                $user->save();
            });
        });
    }
}
