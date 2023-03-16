<?php

namespace App\Observers;

use App;
use App\Actions\Models\PubnubChannel\GetPubnubChannel;
use App\Events\PubnubChannel\Created;
use App\Models\Order\Scopes\BySupplier;
use App\Models\Supplier;
use App\Services\Hubspot\Hubspot;
use Illuminate\Support\Str;

class SupplierObserver
{
    public function creating(Supplier $supplier): void
    {
        $supplier->uuid = Str::uuid();
    }

    public function created(Supplier $supplier): void
    {
        $hubspot = app(Hubspot::class);
        $hubspot->upsertCompany($supplier);
    }

    public function updating(Supplier $supplier): void
    {
        if ($supplier->isDirty('published_at') && !$supplier->getOriginal('published_at')) {
            $supplier->users->each(function($user) use ($supplier) {
                $pubnubChannel = $user->pubnubChannels()->scoped(new BySupplier($supplier))->first();

                if (!$pubnubChannel) {
                    $pubnubChannel = App::make(GetPubnubChannel::class, [
                        'supplier' => $supplier,
                        'user'     => $user,
                    ])->execute();

                    Created::dispatch($pubnubChannel);
                }
            });
        }
    }

    public function updated(Supplier $supplier): void
    {
        $hubspot = app(Hubspot::class);
        $hubspot->upsertCompany($supplier);
    }
}
