<?php

namespace App\Actions\Models\Order;

use App;
use App\Models\Order;
use App\Models\User;

class LogPendingApprovalFirstView
{
    private Order $order;
    private User  $user;

    public function __construct(Order $order, User $user)
    {
        $this->order = $order;
        $this->user  = $user;
    }

    public function execute()
    {
        if ($this->order->isPendingApproval() && !$this->order->pendingApprovalView()->exists()) {
            $this->order->pendingApprovalView()->create([
                'user_id' => $this->user->getKey(),
            ]);
        }
    }
}
