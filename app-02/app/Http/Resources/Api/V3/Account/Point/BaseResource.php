<?php

namespace App\Http\Resources\Api\V3\Account\Point;

use App\Http\Resources\HasJsonSchema;
use App\Models\AppSetting;
use App\Models\SupportCall;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property User $resource
 */
class BaseResource extends JsonResource implements HasJsonSchema
{
    private int $multiplier;

    public function __construct(User $resource, AppSetting $appSettingMultiplier)
    {
        parent::__construct($resource);

        $this->multiplier = $appSettingMultiplier->value;
    }

    public function toArray($request): array
    {
        $user = $this->resource;

        return [
            'available_points'     => $user->availablePoints(),
            'earned_points'        => $user->totalPointsEarned(),
            'cash_value'           => $user->availablePointsToCash(),
            'multiplier'           => $this->multiplier,
            'support_call_enabled' => !($user->isSupportCallDisabled() && ($user->totalPointsEarned() < SupportCall::MINIMUM_POINTS_TO_CALL)),
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'available_points'     => ['type' => ['integer']],
                'earned_points'        => ['type' => ['integer']],
                'cash_value'           => ['type' => ['number']],
                'multiplier'           => ['type' => ['integer']],
                'support_call_enabled' => ['type' => ['boolean']],
            ],
            'required'             => [
                'available_points',
                'earned_points',
                'cash_value',
                'multiplier',
                'support_call_enabled',
            ],
            'additionalProperties' => false,
        ];
    }
}
