<?php

namespace App\Actions\Models\Cart;

use App;
use App\Models\Cart;
use App\Models\User;

class GetCart
{
    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function execute(): Cart
    {
        $cart = $this->user->cart;

        if (!$cart) {
            $supplier = App::make(DefaultSupplier::class, ['user' => $this->user])->execute();

            $cart = $this->user->cart()->create(['supplier_id' => $supplier ? $supplier->getKey() : null]);
        }

        return $cart;
    }
}
