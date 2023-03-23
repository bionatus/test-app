<?php

namespace App\Http\Resources\Api\V3\Part;

use App\Http\Resources\HasJsonSchema;
use App\Models\RelaySwitchTimerSequencer;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Models\RelaySwitchTimerSequencerResource as RelaySwitchTimerSequencerResourceModel;

/**
 * @property RelaySwitchTimerSequencer $resource
 */
class RelaySwitchTimerSequencerResource extends JsonResource implements HasJsonSchema
{
    private RelaySwitchTimerSequencerResourceModel $relaySwitchTimerSequencerResource;

    public function __construct(RelaySwitchTimerSequencer $resource)
    {
        parent::__construct($resource);
        $this->relaySwitchTimerSequencerResource = new RelaySwitchTimerSequencerResourceModel($resource);
    }

    public function toArray($request)
    {
        return $this->relaySwitchTimerSequencerResource->toArray($request);
    }

    public static function jsonSchema(): array
    {
        return RelaySwitchTimerSequencerResourceModel::jsonSchema();
    }
}
