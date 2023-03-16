<?php

namespace App\Actions\Models\PubnubChannel;

use App\Models\Order\Scopes\BySupplier;
use App\Models\PubnubChannel;
use App\Models\Supplier;
use App\Models\User;
use Spatie\QueueableAction\QueueableAction;

class GetPubnubChannel
{
    use QueueableAction;

    private Supplier $supplier;
    private User     $user;

    public function __construct(Supplier $supplier, User $user)
    {
        $this->supplier = $supplier;
        $this->user     = $user;
    }

    public function execute(): PubnubChannel
    {
        /** @var PubnubChannel $pubnubChannel */
        $pubnubChannel = $this->user->pubnubChannels()->scoped(new BySupplier($this->supplier))->first();

        if (!$pubnubChannel) {
            $pubnubChannel = PubnubChannel::create([
                'user_id'     => $this->user->getKey(),
                'supplier_id' => $this->supplier->getKey(),
            ]);
        }

        return $pubnubChannel;
    }
}
