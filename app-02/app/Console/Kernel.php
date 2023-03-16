<?php

namespace App\Console;

use App;
use App\Console\Commands\ExportOrderInvoicesCommand;
use App\Console\Commands\ExportOrderInvoicesSuppliersCommand;
use App\Console\Commands\UpsertXoxoTokensCommand;
use App\Console\Commands\UpsertXoxoVouchersCommand;
use App\Constants\Timezones;
use App\Jobs\SendOrderPendingApprovalReminder;
use Config;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [];

    protected function schedule(Schedule $schedule)
    {
        $exportOrderInvoicesDay               = Config::get('scheduler.export-order-invoices.day');
        $exportOrderInvoicesHour              = Config::get('scheduler.export-order-invoices.hour');
        $exportOrderInvoicesTimezone          = Config::get('scheduler.export-order-invoices.timezone');
        $sendOrderPendingApprovalReminderHour = Config::get('scheduler.send-order-pending-approval-reminder.hour');
        $xoxoUpdateVouchersDaily              = Config::get('scheduler.xoxo-update-vouchers.day');

        $schedule->command(ExportOrderInvoicesCommand::class)
            ->monthlyOn($exportOrderInvoicesDay, $exportOrderInvoicesHour)
            ->timezone($exportOrderInvoicesTimezone);
        $schedule->command(ExportOrderInvoicesSuppliersCommand::class)
            ->monthlyOn($exportOrderInvoicesDay, $exportOrderInvoicesHour)
            ->timezone($exportOrderInvoicesTimezone);

        foreach (Timezones::ALLOWED_TIMEZONES as $timezone) {
            $schedule->job(new SendOrderPendingApprovalReminder($timezone))
                ->dailyAt($sendOrderPendingApprovalReminderHour)
                ->timezone($timezone);
        }

        $schedule->job(new SendOrderPendingApprovalReminder(null))
            ->dailyAt($sendOrderPendingApprovalReminderHour)
            ->timezone(Timezones::AMERICA_LOS_ANGELES);

        $schedule->command(UpsertXoxoTokensCommand::class)->weekly();
        $schedule->command(UpsertXoxoVouchersCommand::class)->dailyAt($xoxoUpdateVouchersDaily);
    }

    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
