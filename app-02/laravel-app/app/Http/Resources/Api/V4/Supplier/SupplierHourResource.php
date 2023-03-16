<?php

namespace App\Http\Resources\Api\V4\Supplier;

use App\Http\Resources\HasJsonSchema;
use App\Types\SupplierWorkingHour;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/**
 * @property SupplierWorkingHour $resource
 */
class SupplierHourResource extends JsonResource implements HasJsonSchema
{
    public function __construct(SupplierWorkingHour $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        $supplierHour = $this->resource;
        $day          = $supplierHour->date();
        $timezone     = $supplierHour->timezone();

        return [
            'from' => $this->parseDateToUtc($day, $supplierHour->from(), $timezone),
            'to'   => $this->parseDateToUtc($day, $supplierHour->to(), $timezone),
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'from' => ['type' => ['string']],
                'to'   => ['type' => ['string']],
            ],
            'required'             => [
                'from',
                'to',
            ],
            'additionalProperties' => false,
        ];
    }

    private function parseDateToUtc(string $day, string $hourA, string $timezone): string
    {
        return Carbon::createFromFormat('d/m/Y H:i A', $day . $hourA, $timezone)->utc()->toISOString();
    }
}
