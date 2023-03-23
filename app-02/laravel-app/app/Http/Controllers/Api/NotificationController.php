<?php

namespace App\Http\Controllers\Api;

use App\AppNotification;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\JWTAuth;

class NotificationController extends Controller
{
    /**
     * Notifications times
     *
     * @var array
     */
    protected $times = [
        '1h'  => 60 * 60,
        '24h' => 24 * 60 * 60,
        '1w'  => 7 * 24 * 60 * 60,
    ];

    public function create(Request $request)
    {
        return \Response::json([])->setStatusCode(Response::HTTP_OK);
    }

    /**
     * Remove Airship notification
     *
     * @param Illuminate\Http\Request $request
     *
     * @return Illuminate\Http\JsonResponse
     */
    public function remove(Request $request)
    {
        $tagName     = $request->get('tagName');
        $currentTime = Carbon::now('UTC');

        $notifications = AppNotification::where([
            ['tag_name', '=', $tagName],
            ['date', '>', $currentTime],
        ])->get();

        if (empty($notifications)) {
            return;
        }
    }

    /**
     * Get User Notifications
     *
     * @param Illuminate\Http\Request $request
     * @param JWTAuth\JWTAuth         $auth
     *
     * @return Illuminate\Http\JsonResponse
     */
    public function get(Request $request, JWTAuth $auth)
    {
        $user = $auth->user();

        return $user->app_notifications->map(function($notification) {
            if ($notification->date > Carbon::now('UTC')) {
                return null;
            }

            return [
                'read' => $notification->read ? 'read' : 'unread',
                'date' => Carbon::parse($notification->date)->format('Y-m-d H:i:s'),
                'text' => $notification->message,
            ];
        })->filter()->sortByDesc('date')->groupBy('read')->toArray();
    }

    /**
     * Get User Notifications Status
     *
     * @param Illuminate\Http\Request $request
     * @param JWTAuth\JWTAuth         $auth
     *
     * @return Illuminate\Http\JsonResponse
     */
    public function status(Request $request, JWTAuth $auth)
    {
        $user = $auth->user();

        $hasNewNotifications = $user->app_notifications->search(function($item, $key) {
            return !$item->read && $item->date->lt(Carbon::now('UTC'));
        });

        return [
            'newNotifications' => $hasNewNotifications || $hasNewNotifications === 0,
        ];
    }

    /**
     * Mark User Notifications as read
     *
     * @param Illuminate\Http\Request $request
     * @param JWTAuth\JWTAuth         $auth
     *
     * @return void
     */
    public function read(Request $request, JWTAuth $auth)
    {
        $user = $auth->user();

        $notifications = AppNotification::where([
                ['user_id', '=', $user->id],
                ['date', '<=', Carbon::now('UTC')],
            ])->whereNull('read')->get();

        $notifications->each(function($notification) {
            $notification->read = true;
            $notification->save();
        });
    }
}
