<?php

namespace App\Actions\Models\Supplier;

use App\Models\Supplier;
use Exception;
use Illuminate\Support\Carbon;

class GetWorkingHoursByDays
{
    private Supplier $supplier;
    private string   $date;

    public function __construct(Supplier $supplier, string $date = '')
    {
        $this->supplier = $supplier;
        $this->date     = $date;
    }

    public function execute()
    {
        $workingHoursByDay = [
            'time_ranges' => [],
            'start_time'  => [],
            'end_time'    => [],
        ];

        try {
            $currentDay   = Carbon::createFromFormat('Y-m-d H:i', $this->date, 'UTC')->timezone($this->supplier->timezone);
            $dayOfTheWeek = strtolower($currentDay->englishDayOfWeek);
        } catch (Exception $exception) {
            return $workingHoursByDay;
        }

        if (!$supplierHour = $this->supplier->supplierHours()->where('day', $dayOfTheWeek)->first()) {
            return $workingHoursByDay;
        }

        $from = Carbon::createFromFormat('Y-m-d g:i a', $currentDay->format('Y-m-d') . ' ' . $supplierHour->from,
            $this->supplier->timezone)->utc();
        $to   = Carbon::createFromFormat('Y-m-d g:i a', $currentDay->format('Y-m-d') . ' ' . $supplierHour->to,
            $this->supplier->timezone)->utc();

        if($from >= $to){
            $to->addDay();
        }

        $hour = 0;
        while ($from->clone()->addHours($hour + 1) <= $to) {
            $startHour = $from->clone()->addHours($hour);
            $hour++;
            $endHour                            = $from->clone()->addHours($hour);
            $workingHoursByDay['time_ranges'][] = [
                'start' => $startHour->format('H:i'),
                'end'   => $endHour->format('H:i'),
            ];
            $workingHoursByDay['start_time'][$startHour->format('H:i')]  = $startHour;
            $workingHoursByDay['end_time'][$endHour->format('H:i')]    = $endHour;
        }

        return $workingHoursByDay;
    }
}
