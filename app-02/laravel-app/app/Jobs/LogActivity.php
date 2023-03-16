<?php

namespace App\Jobs;

use App\Models\Activity;
use App\Types\ActivityDataType;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class LogActivity implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $event;
    protected string $resource;
    protected Model  $model;
    protected ?Model $causer;
    protected array  $property;
    protected string $logName;

    public function __construct(
        string $event,
        string $resource,
        Model $model,
        ?Model $causer,
        array $property,
        string $logName = Activity::TYPE_FORUM
    ) {
        $this->event    = $event;
        $this->resource = $resource;
        $this->model    = $model->withoutRelations();
        $this->causer   = $causer;
        $this->property = $property;
        $this->logName  = $logName;
    }

    public function handle()
    {
        /** @var Activity $activity */
        $activity = activity($this->logName)
            ->performedOn($this->model)
            ->causedBy($this->causer)
            ->withProperties($this->property)
            ->tap(function(Activity $activity) {
                $activity->event    = $this->event;
                $activity->resource = $this->resource;
            })
            ->log($this->resource . '.' . $this->event);

        if ($relatedActivityData = (new ActivityDataType($activity))->toArray()) {
            $activity->relatedActivity()->create($relatedActivityData);
        }
    }
}
