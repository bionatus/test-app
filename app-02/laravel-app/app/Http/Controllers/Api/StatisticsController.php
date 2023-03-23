<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Tymon\JWTAuth\JWTAuth;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\SemaphoreStore;
use App\Services\Hubspot\Hubspot;

class StatisticsController extends Controller
{
    /**
     * Update Statistics Data
     *
     * @param  Illuminate\Http\Request $request
     * @return void
     */
    public function update(Request $request)
    {
        $type = $request->get('type');

        $user = auth()->user();

        $currentCount = $user[$type];
        $user[$type] = $currentCount ? ++$currentCount : 1;

        $user->save();

        $this->updateHubspotCallsCounter($type, $user);
    }

    /**
     * Update Hubspot Data
     *
     * @param  Illuminate\Http\Request $request
     * @return void
     */
    public function updateHubspotCallsCounter($type, $user)
    {
        if ($type !== 'calls_count') {
            return;
        }

        $store = new SemaphoreStore();
        $factory = new LockFactory($store);

        $lock = $factory->createLock('call-request-' . $user->id);

        if ($lock->acquire()) {
            // Change Hubpost Call field
            try {
                $hubspot = app(Hubspot::class);
                $contact = $hubspot->updateUserSupportCallCount($user);
            } catch (Exception $e) {
                $lock->release();
                return response()->json('Could not update call date.', 401);
            }

            $lock->release();
        }
    }
}
