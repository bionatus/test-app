<?php

namespace App\Actions\Models\Cart;

use App;
use App\Models\Order;
use App\Models\Scopes\Newest;
use App\Models\Supplier;
use App\Models\Supplier\Scopes\ByOnTheNetwork;
use App\Models\Supplier\Scopes\ByPreferred;
use App\Models\Supplier\Scopes\NearToCoordinates;
use App\Models\User;

class DefaultSupplier
{
    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function execute(): ?Supplier
    {
        $preferredSupplier = $this->user->visibleSuppliers()
            ->scoped(new ByPreferred())
            ->scoped(new ByOnTheNetwork())
            ->first();
        if ($preferredSupplier) {
            return $preferredSupplier;
        }

        /** @var Order $lastRequestedOrder */
        $lastRequestedOrder = $this->user->orders()->scoped(new Newest())->first();

        if ($lastRequestedOrder) {
            return $lastRequestedOrder->supplier;
        }

        $userSupplier = $this->user->visibleSuppliers()->scoped(new ByOnTheNetwork());
        $company      = $this->user->company()->first();
        if ($company && $company->hasValidZipCode() && $company->hasValidCoordinates()) {
            $userSupplier->scoped(new NearToCoordinates($company->latitude, $company->longitude));
        }

        return $userSupplier->first();
    }
}
