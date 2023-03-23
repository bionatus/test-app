<?php

namespace App\Http\Resources\Api\V3\OrderSupplier;

use App\Http\Resources\HasJsonSchema;
use App\Models\SupplierHour;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/**
 * @property SupplierHour $resource
 */
class SupplierHourResource extends JsonResource implements HasJsonSchema
{
    public function __construct(SupplierHour $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        $supplierHour = $this->resource;
        $day          = $supplierHour->day;

        return [
            'from' => $this->parseDateToUtc($day, $supplierHour->from),
            'to'   => $this->parseDateToUtc($day, $supplierHour->to),
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

    private function parseDateToUtc(string $dayName, string $date): string
    {
        return Carbon::parse($dayName . ' ' . $date, $this->resource->supplier->timezone)->utc()->toISOString();
    }
}
