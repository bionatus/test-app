<?php

namespace App\Services\CustomerSupport\Call;

use App\Models\Agent;
use App\Models\Call;
use App\Models\User;

interface ResponseInterface
{
    public function retryAgainLater(): string;

    public function thanksForCalling(): string;

    public function connect(Call $call, User $user, Agent $agent): string;

    public function hangUp(): string;

    public function technicalDifficulties(): string;
}
