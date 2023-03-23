<?php

namespace App\Events\User;

use App\Models\User;

interface UserEventInterface
{
    public function __construct(User $user);

    public function user(): User;
}
