<?php

namespace App\Nova\Actions;

use App\Exports\UsersExport;
use App\Jobs\SendUsersExport;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class ExportUsers extends Action
{
    use InteractsWithQueue, Queueable;

    protected $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Perform the action on the given models.
     *
     * @param \Laravel\Nova\Fields\ActionFields $fields
     * @param \Illuminate\Support\Collection    $models
     *
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $export = new UsersExport($models);
        $export->queue('users.csv', null, \Maatwebsite\Excel\Excel::CSV)->chain([
                new SendUsersExport($this->user),
            ]);

        return Action::message('Users export started! You will be notified by email when it is ready!');
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [];
    }
}
