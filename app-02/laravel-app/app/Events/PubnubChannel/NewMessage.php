<?php

namespace App\Events\PubnubChannel;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

abstract class NewMessage implements NewMessageEventInterface
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected string   $message;
    protected Supplier $supplier;
    protected User     $user;

    public function __construct(Supplier $supplier, User $user, string $message)
    {
        $this->message  = $message;
        $this->supplier = $supplier;
        $this->user     = $user;
    }

    public function message(): string
    {
        return $this->message;
    }

    public function supplier(): Supplier
    {
        return $this->supplier;
    }

    public function user(): User
    {
        return $this->user;
    }
}
