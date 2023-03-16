<?php

namespace App\Nova\Resources;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;

class AppVersion extends Resource
{
    public static $model  = \App\Models\AppVersion::class;
    public static $title  = 'current';
    public static $search = [
        'min',
        'current',
        'video_title',
        'video_url',
        'message',
    ];
    public static $group  = 'Current';

    public function fields(Request $request)
    {
        return [
            Text::make(__('Min'), 'min')->rules(['required', 'regex:/^(?:(\d+)\.)(?:(\d+)\.)(\*|\d+)$/']),
            Text::make(__('Current'), 'current')->rules(['required', 'regex:/^(?:(\d+)\.)(?:(\d+)\.)(\*|\d+)$/']),
            Text::make(__('Video Title'), 'video_title'),
            Text::make(__('Video URL'), 'video_url')->rules(['nullable', 'url']),
            Textarea::make(__('Message'), 'message')->alwaysShow()->rules(['required']),
        ];
    }
}
