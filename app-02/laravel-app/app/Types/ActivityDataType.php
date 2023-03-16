<?php

namespace App\Types;

use App\Models\Activity;

class ActivityDataType
{
    private Activity $activity;
    private ?string  $event    = null;
    private ?string  $resource = null;
    private ?int     $userId   = null;

    public function __construct(Activity $activity)
    {
        $this->activity = $activity;

        $this->map();
    }

    private function map(): void
    {
        switch ([$this->activity->event, $this->activity->resource]) {
            case [Activity::ACTION_CREATED, Activity::RESOURCE_COMMENT]:
                $this->event    = Activity::ACTION_REPLIED;
                $this->resource = Activity::RESOURCE_POST;
                $this->userId   = $this->activity->properties['post']['user']['id'];
                break;

            case [Activity::ACTION_CREATED, Activity::RESOURCE_SOLUTION]:
                $this->event    = Activity::ACTION_SELECTED;
                $this->resource = Activity::RESOURCE_COMMENT;
                $this->userId   = $this->activity->properties['user']['id'];
                break;
        }
    }

    public function toArray(): array
    {
        if (!$this->event) {
            return [];
        }

        return [
            'event'    => $this->event,
            'resource' => $this->resource,
            'user_id'  => $this->userId,
        ];
    }
}
