<?php

namespace App\Jobs\Supplier;

use App;
use App\Models\Staff;
use App\Models\Supplier;
use App\Models\SupplierHour;
use Hash;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Mail;
use Str;

class SyncStaff implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        $suppliers = Supplier::cursor();
        $records   = new Collection();

        $suppliers->each(function(Supplier $supplier) use ($records) {

            $supplier->staff()->create([
                'type'     => Staff::TYPE_OWNER,
                'email'    => $supplier->email,
                'password' => Hash::make($password = Str::padRight($supplier->airtable_id, 8, '0')),
            ]);

            $records->push([
                'id'       => $supplier->airtable_id,
                'email'    => $supplier->email,
                'password' => $password,
            ]);

            $from       = '9:00 am';
            $weekDayTo  = '5:00 pm';
            $saturdayTo = '1:00 pm';

            $days = Collection::make([
                SupplierHour::DAY_MONDAY,
                SupplierHour::DAY_TUESDAY,
                SupplierHour::DAY_WEDNESDAY,
                SupplierHour::DAY_THURSDAY,
                SupplierHour::DAY_FRIDAY,
            ]);

            $days->each(function(string $day) use ($supplier, $from, $weekDayTo) {
                $supplier->supplierHours()->create([
                    'day'  => $day,
                    'from' => $from,
                    'to'   => $weekDayTo,
                ]);
            });
            $supplier->supplierHours()->create([
                'day'  => SupplierHour::DAY_SATURDAY,
                'from' => $from,
                'to'   => $saturdayTo,
            ]);
        });

        $mailable = new App\Mail\Supplier\SyncStaffReportEmail($records);
        $mailable->subject('Created staff report');
        Mail::to(['robert.lopez@devbase.us', 'alejandro.rohmer@devbase.us'])->send($mailable);
    }
}
