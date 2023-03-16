<?php

namespace App\Http\Controllers\Api;

use App\Models\User as LatamUser;
use App\Http\Resources\Api\V2\User\Setting\BaseResource;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Tymon\JWTAuth\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Storage;
use App\User;
use Coduo\PHPHumanizer\NumberHumanizer;
use Intervention\Image\Facades\Image;

class UsersController extends Controller
{
    /**
     * Handle a user request to the application.
     *
     * @param  Illuminate\Http\Request $request
     * @param  Tymon\JWTAuth\JWTAuth $auth
     * @return Illuminate\Http\JsonResponse
     */
    public function index(Request $request, JWTAuth $auth)
    {
        $user = $auth->user();

        $formatAddress = implode(', ', collect([$user->city, $user->state, $user->country])->filter()->toArray());

        $latamUser = LatamUser::find($user->getKey());

        return response()->json([
            'id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
            'firstName' => $user->first_name,
            'lastName' => $user->last_name,
            'phone' => $user->phone,
            'accreditated' => $user->accreditated,
            'address' => $user->address,
            'city' => $user->city,
            'state' => $user->state,
            'country' => $user->country,
            'formattedAddress' => $formatAddress,
            'company' => $user->company,
            'photo' => !empty($user->photo) ? asset(Storage::url($user->photo)) : '',
            'bio' => $user->bio,
            'jobTitle' => $user->job_title,
            'union' => $user->union,
            'experience' => $user->experience_years,
            'memberSince' => !empty($user->registration_completed_at) ? $user->registration_completed_at->format('Y-m-d') : $user->created_at->format('Y-m-d'),
            'unread_notifications' => $latamUser->getUnreadNotificationsCount(),
            'is_agent' => $latamUser->isAgent(),
            'settings' => BaseResource::collection($latamUser->allSettingUsers()),
        ]);
    }

    /**
     * Handle an update user request to the application.
     *
     * @param  Illuminate\Http\Request $request
     * @param  Tymon\JWTAuth\JWTAuth $auth
     * @return Illuminate\Http\JsonResponse
     */
    public function update(Request $request, JWTAuth $auth)
    {
        $user = $auth->user();

        $user->first_name = $request->firstName;
        $user->last_name = $request->lastName;
        $user->name = $request->firstName . ' ' . $request->lastName;
        $user->city = $request->city;
        $user->state = $request->state;
        $user->country = $request->country;
        $user->experience_years = $request->experience;
        $user->bio = $request->bio;
        $user->company = $request->company;
        $user->job_title = $request->jobTitle;
        $user->union = $request->union;
        $user->phone = $request->phone;
        $user->address = $request->address;

        if (!empty($request->newImage)) {
            $filename = md5('profile-image' . time()) . '.jpg';
            $img = Image::make($request->newImage);
            $path = $img->save(storage_path() . '/app/public/' . $filename);
            $user->photo = $filename;
        }

        $user->save();
    }

    /**
     * Handle a count users request to the application.
     *
     * @param  Illuminate\Http\Request $request
     * @param  Tymon\JWTAuth\JWTAuth $auth
     * @return Integer
     */
    public function count(Request $request, JWTAuth $auth)
    {
        $usersCount = User::where('registration_completed', true)->count();

        return NumberHumanizer::metricSuffix($usersCount);
    }

    /**
     * Handle a count accreditated users request to the application.
     *
     * @param  Illuminate\Http\Request $request
     * @param  Tymon\JWTAuth\JWTAuth $auth
     * @return Integer
     */
    public function countAccreditated(Request $request, JWTAuth $auth)
    {
        $usersCount = User::where('accreditated', true)->count();

        return number_format($usersCount, 0, '.', ',');
    }
}
