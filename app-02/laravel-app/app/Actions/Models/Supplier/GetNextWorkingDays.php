<?php

namespace App\Actions\Models\Supplier;

use App\Models\Supplier;
use App\Models\SupplierHour;
use App\Types\SupplierWorkingHour;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class GetNextWorkingDays
{
    private Supplier $supplier;

    public function __construct(Supplier $supplier)
    {
        $this->supplier = $supplier;
    }

    public function execute()
    {
        $workingDaysOfTheWeek = [];
        $indexedSupplierHours = [];
        foreach ($this->supplier->supplierHours as $item) {
            if (!in_array(strtolower($item->day), $workingDaysOfTheWeek)) {
                $workingDaysOfTheWeek[]                       = strtolower($item->day);
                $indexedSupplierHours[strtolower($item->day)] = [
                    'from'     => $item->from,
                    'to'       => $item->to,
                    'timezone' => $this->supplier->timezone,
                ];
            }
        }
        $nextWorkingDays = new Collection();
        if (!count($workingDaysOfTheWeek)) {
            return $nextWorkingDays;
        }
        $dayCounter       = 0;
        $validWorkingDays = 0;
        while ($validWorkingDays < SupplierHour::MAX_WORKING_DAYS) {
            $currentDay   = Carbon::now()->addDays($dayCounter);
            $dayOfTheWeek = strtolower($currentDay->englishDayOfWeek);
            if (in_array($dayOfTheWeek, $workingDaysOfTheWeek)) {
                $nextWorkingDays->push(new SupplierWorkingHour(
                    "$currentDay->day/$currentDay->month/$currentDay->year",
                    $indexedSupplierHours[$dayOfTheWeek]['from'],
                    $indexedSupplierHours[$dayOfTheWeek]['to'],
                    $indexedSupplierHours[$dayOfTheWeek]['timezone']));
                $validWorkingDays++;
            }
            $dayCounter++;
        }

        return $nextWorkingDays;
    }
}
