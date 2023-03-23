<?php

namespace App\Policies\Api\V2;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TicketPolicy
{
    use HandlesAuthorization;

    public function close(User $user, Ticket $ticket): bool
    {
        return $ticket->isOwner($user) || ($user->isAgent() && $ticket->isActiveParticipant($user->agent));
    }

    public function rate(User $user, Ticket $ticket): bool
    {
        if (!$ticket->isOwner($user)) {
            return false;
        }

        return $ticket->isClosed();
    }

    public function agentRate(User $user, Ticket $ticket): bool
    {
        if (!$ticket->isClosed()) {
            return false;
        }

        if (!$user->isAgent()) {
            return false;
        }

        return $ticket->isActiveParticipant($user->agent);
    }

    public function seeAgentHistory(User $user): bool
    {
        return $user->isAgent();
    }
}
