<?php

namespace App\Console\Commands;

use App;
use App\Models\XoxoVoucher;
use App\Services\Xoxo\Xoxo;
use App\Types\XoxoVoucher as XoxoVoucherType;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class UpsertXoxoVouchersCommand extends Command
{
    protected $signature   = 'xoxo:upsert-vouchers';
    protected $description = 'Upsert Xoxo vouchers';

    /**
     * @throws \App\Exceptions\XoxoException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function handle()
    {
        $this->info('[' . Carbon::now()->format('Y-m-d H:i:s') . ']' . ' Starting process');
        $bar         = $this->output->createProgressBar();
        $xoxoService = App::make(Xoxo::class);

        $bar->start();
        $vouchers = $xoxoService->getRedeemMethods();
        $bar->setMaxSteps($vouchers->count());

        XoxoVoucher::query()->update(['published_at' => null]);
        $now = Carbon::now();

        $vouchers->each(function(XoxoVoucherType $voucher) use ($bar, $now) {
            XoxoVoucher::updateOrCreate([
                'code' => $voucher->code(),
            ], [
                'code'                => $voucher->code(),
                'name'                => $voucher->name(),
                'image'               => $voucher->image(),
                'value_denominations' => $voucher->valueDenominations()->sort()->join(','),
                'description'         => $voucher->description(),
                'instructions'        => $voucher->instructions(),
                'terms_conditions'    => $voucher->termsConditions(),
                'published_at'        => $now,
            ]);
            $bar->advance();
        });

        $bar->finish();
        $this->info('[' . Carbon::now()->format('Y-m-d H:i:s') . ']' . ' Finished process');
    }
}
