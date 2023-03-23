<?php

namespace App\Events\PubnubChannel;

use App\Models\Supplier;
use App\Models\User;

interface NewMessageEventInterface
{
    public function __construct(Supplier $supplier, User $user, string $message);

    public function message(): string;

    public function supplier(): Supplier;

    public function user(): User;
}
