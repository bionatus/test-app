<?php

namespace App\Jobs\OrderInvoice;

use App;
use App\Models\OrderInvoice;
use App\Models\OrderInvoice\Scopes\ByCreatedBetween;
use App\Models\OrderInvoice\Scopes\NotProcessed;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class ToProcess implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        $fromDate = Carbon::now()->subMonth()->startOfMonth()->toDateTimeString();
        $tillDate = Carbon::now()->subMonth()->endOfMonth()->toDateTimeString();

        $orderInvoices = OrderInvoice::with(['order.supplier', 'order.user.companyUser.company'])
            ->scoped(new ByCreatedBetween($fromDate, $tillDate))
            ->scoped(new NotProcessed());
        $orderInvoices->update(['processed_at' => Carbon::now()]);
    }
}
