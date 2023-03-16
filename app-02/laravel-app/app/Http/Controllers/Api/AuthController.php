<?php

namespace App\Http\Controllers\Api;

use App\Constants\RequestKeys;
use App\Http\Requests\Api\LoginRequest;
use App\Models\Device;
use Exception;
use Illuminate\Http\Request;
use Tymon\JWTAuth\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

use App\User;
use App\Http\Controllers\Controller;
use App\Traits\HandleWordPressPasswords;
use App\Traits\CheckEmailOrUsername;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Services\Nutshell\Nutshell;
use App\Services\Hubspot\Hubspot;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\AccreditationEmail;
use Illuminate\Support\Facades\Password;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\SemaphoreStore;
use Illuminate\Support\Facades\Storage;
use App\Models\AppNotification;

class AuthController extends Controller
{
    use SendsPasswordResetEmails, HandleWordPressPasswords, CheckEmailOrUsername {
        CheckEmailOrUsername::checkEmailOrUsername insteadof HandleWordPressPasswords;
    }

    /**
     * Accreditation process running flag
     *
     * @var boolean
     */
    public $accreditationProcessRunning = false;

    /**
     * Handle a login request to the application.
     *
     * @param  App\Http\Requests\Api\LoginRequest $request
     * @param  Tymon\JWTAuth\JWTAuth $auth
     * @return Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request, JWTAuth $auth)
    {
        $login_field = $this->checkEmailOrUsername($request->username);

        $credentials = [
            $login_field => $request->username,
            'password' => $request->password
        ];

        $this->handleWordPressPassword([
            'login' => $request->username,
            'password' => $request->password
        ]);

        try {

            $token = $auth->attempt($credentials);

            if (!$token) {
                return response()->json(['data' => trans('auth.failed')], 401);
            }

            $user = $auth->user($token);

            if ($request->get(RequestKeys::DEVICE)){
                $this->storeDeviceVersion($user->id, $token, $request->get(RequestKeys::DEVICE), $request->get(RequestKeys::VERSION));
            }

            $hasNewNotifications = $user->app_notifications->search(function($item, $key) {
                return !$item->read && $item->date->lt(Carbon::now('UTC'));
            });

            return response()->json([
                'token' => $token,
                'userId' => $user->id,
                'firstName' => $user->first_name,
                'accreditated' => $user->accreditated ? true : false,
                'newNotifications' => $hasNewNotifications || $hasNewNotifications === 0,
                'registrationCompleted' => $user->registration_completed ? true : false,
                'photo' => !empty($user->photo) ? asset(Storage::url($user->photo)) : '',
                'terms' => $user->terms ? true : false,
            ]);
        } catch (JWTException $e) {
            return response()->json('Could not create the token.', 401);
        }

    }

    /**
     * Log the user out of the application.
     *
     * @param  Illuminate\Http\Request $request
     * @return Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        auth()->logout();

        return response()->noContent();
    }

     /**
     * Handle refresh token request of the application.
     *
     * @param  Illuminate\Http\Request $request
     * @return Illuminate\Http\JsonResponse
     */
    public function refreshToken(Request $request, JWTAuth $auth)
    {
        $token = $auth->refresh(true, true);
        $user = $auth->user($token);

        return response()->json([
            'token' => $token,
            'userId' => $user->id,
            'firstName' => $user->first_name,
            'accreditated' => $user->accreditated ? true : false,
            'photo' => !empty($user->photo) ? asset(Storage::url($user->photo)) : '',
            'registrationCompleted' => $user->registration_completed ? true : false,
            'terms' => $user->terms ? true : false,
        ]);
    }


    /**
     * Handle a login request from application through WordPress.
     *
     * @param  App\Http\Requests\Api\LoginRequest $request
     * @param  Tymon\JWTAuth\JWTAuth $auth
     * @return Illuminate\Http\JsonResponse
     */
    public function legacyLogin(Request $request, JWTAuth $auth)
    {
        $login_field = $this->checkEmailOrUsername($request->username);

        $credentials = [
            $login_field => $request->username,
            'password' => $request->password
        ];

        $this->handleWordPressPassword([
            'login' => $request->username,
            'password' => $request->password
        ]);

        try {
            $token = $auth->attempt($credentials);

            if (!$token) {
                return response()->json(['data' => trans('auth.failed')], 401);
            }

            $user = $auth->user();
        } catch (JWTException $e) {
            return response()->json('Could not create the token.', 401);
        }

        return response()->json([
            'token' => $token,
            'id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
            'username' => $user->user_login,
        ]);
    }

    /**
     * Handle a user request.
     *
     * @param  App\Http\Requests\Api\LoginRequest $request
     * @param  Tymon\JWTAuth\JWTAuth $auth
     * @return Illuminate\Http\JsonResponse
     */
    public function getUser(Request $request, JWTAuth $auth)
    {
        try {
            $token = $auth->refresh(true, true);
            $user = $auth->user();
        } catch (JWTException $e) {
            return response()->json('Could not retrieve user data.', 401);
        }

        return response()->json([
            'token'                => $token,
            'id'                   => $user->id,
            'email'                => $user->email,
            'name'                 => $user->name,
            'username'             => $user->user_login,
        ]);
    }

    /**
     * Handle a create user request.
     *
     * @param  App\Http\Requests\Api\LoginRequest $request
     * @param  Tymon\JWTAuth\JWTAuth $auth
     * @return Illuminate\Http\JsonResponse
     */
    public function createAccount(Request $request, JWTAuth $auth)
    {
        $validation_rules = [
            'email' => 'required|email:strict|ends_with_tld|unique:users',
            'firstName' => 'required',
            'lastName' => 'required',
            'password' => 'required',
            'confirmPassword' => 'required|same:password',
            'phone' => 'required',
            // 'address' => 'required',
            // 'city' => 'required',
            // 'state' => 'required',
            'zip' => 'required',
            // 'company' => 'required',
            // 'occupation' => 'required',
            // 'typeOfServices' => 'required',
            // 'techsNumber' => 'required',
            // 'supplier' => 'required',
        ];

        $validator = Validator::make($request->data, $validation_rules);

        if ($validator->fails()) {
            return [
                'status' => 'error',
                'errors' => $validator->messages(),
            ];
        }

        // $user = new User();

        $user = User::withoutEvents(function () {
            return new User();
        });

        $user->first_name = $this->stringOrNull($request->data['firstName']);
        $user->last_name = $this->stringOrNull($request->data['lastName']);
        $user->phone = $this->stringOrNull($request->data['phone']);
        $user->email = $this->stringOrNull($request->data['email']);
        $user->address = $this->stringOrNull($request->data['address']);
        $user->city = $this->stringOrNull($request->data['city']);
        $user->state = $this->stringOrNull($request->data['state']);
        $user->zip = $this->stringOrNull($request->data['zip']);
        // $user->country = $this->stringOrNull($request->data['country']);
        $user->company = $this->stringOrNull($request->data['company']);
        $user->occupation = $this->stringOrNull($request->data['occupation']);
        $user->type_of_services = $this->stringOrNull($request->data['typeOfServices']);
        $user->hvac_supplier = $this->stringOrNull($request->data['supplier']);
        $user->name = $user->first_name . ' ' . $user->last_name;

        // Legacy data. Removed in app v4.2.6
        if (!empty($request->data['employees'])) {
            $user->employees = $this->stringOrNull($request->data['employees']);
        }

        $user->techs_number = $this->stringOrNull($request->data['techsNumber']);
        $user->service_manager_name = $this->stringOrNull($request->data['serviceManagerName']);
        $user->service_manager_phone = $this->stringOrNull($request->data['serviceManagerPhone']);

        if (!empty( $request->data['phone'])) {
            $user->phone = $this->stringOrNull($request->data['phone']);
        }

        $user->password = Hash::make($request->data['password']);

        if (!empty($request->accreditated) && $request->accreditated === true) {
            $user->accreditated = true;
        }

        // Accreditate the user if Group Code bluon458 is used
        if (!empty($request->data['groupCode']) && $request->data['groupCode'] === 'bluon458') {
           $user->accreditated = true;
           $user->group_code = $request->data['groupCode'];
        }

        if (!empty($request->data['apps'])) {
            $user->apps = $request->data['apps'];
        }

        $user->save();

        $credentials = [
            'email' => $user->email,
            'password' => $request->data['password']
        ];

        try {
            $token = $auth->attempt($credentials);

            if (!$token) {
                return response()->json(['data' => trans('auth.failed')], 401);
            }
        } catch (JWTException $e) {
            return response()->json('Could not create the token.', 401);
        }

        try {
            $hubspot = app(Hubspot::class);

            $contact = $hubspot->upsertContact($user);

            if ($contact && $contact->getId()) {
                $user->hubspot_id = $contact->getId();
                $user->save();

                $company = $hubspot->createCompany($user, $contact->getId());

                if ($company) {
                    $hubspot->associateCompanyContact($company->getId(), $contact->getId());
                }
            }
        } catch (Exception $e) {
            return response()->json('Could not create account.', 401);
        }

        return [
            'status' => 'success',
            'token' => $token,
            'userId' => $user->id,
            'firstName' => $user->first_name,
            'accreditated' => $user->accreditated,
            'photo' => !empty($user->photo) ? asset(Storage::url($user->photo)) : '',
            'registrationCompleted' => $user->registration_completed,
            'terms' => $user->terms ? true : false,
        ];
    }

    /**
     * Handle accreditation complete.
     *
     * @param  App\Http\Requests\Api\LoginRequest $request
     * @return Illuminate\Http\JsonResponse
     */
    public function completeAccreditation(Request $request, JWTAuth $auth)
    {
        $store = new SemaphoreStore();
        $factory = new LockFactory($store);

        if ($request->userId) {
            $user = User::where('id', $request->userId)->firstOrFail();
        } else {
            $user = auth()->user();
        }

        $lock = $factory->createLock('pdf-generation-' . $user->id);

        if ($lock->acquire()) {
            $user->accreditated = true;
            $user->accreditated_at = Carbon::now();
            $user->save();

            // Send Accreditation Email
            $this->sendAccreditationEmail($user);

            // Change Hubspot Accreditation field
            try {
                $hubspot = app(Hubspot::class);
                $hubspot->setUserAccreditation($user);
            } catch (Exception $e) {
                $lock->release();
                return response()->json('Could not update accreditation.', 401);
            }

            $lock->release();

            return [
                'status' => 'success',
                'userId' => $user->id,
            ];
        }
    }

    /**
     * Handle registration complete.
     *
     * @param  App\Http\Requests\Api\LoginRequest $request
     * @return Illuminate\Http\JsonResponse
     */
    public function completeRegistration(Request $request)
    {

        $user = User::where('id', $request->userId)->firstOrFail();
        $user->registration_completed = true;
        $user->registration_completed_at = Carbon::now();
        $user->access_code = $request->accessCode;
        $user->save();

        // Change Hubspot Accreditation field
        try {
            $hubspot = app(Hubspot::class);
            $contact = $hubspot->setUserRegistration($user);
        } catch (Exception $e) {
            return response()->json('Could not update user.', 401);
        }
        return [
            'status' => 'success',
            'userId' => $user->id,
        ];
    }

    /**
     * Handle accept terms
     *
     * @param  App\Http\Requests\Api\LoginRequest $request
     * @return Illuminate\Http\JsonResponse
     */
    public function acceptTerms(Request $request)
    {
        $user = auth()->user();
        $user->terms = true;
        $user->save();

        return [
            'status' => 'success',
            'userId' => $user->id,
            'registrationCompleted' => $user->registration_completed,
            'terms' => $user->terms ? true : false,
        ];
    }

    /**
     * Handle call button request
     *
     * @param  App\Http\Requests\Api\LoginRequest $request
     * @return Illuminate\Http\JsonResponse
     */
    public function handleCallRequest(Request $request)
    {
        $store = new SemaphoreStore();
        $factory = new LockFactory($store);
        $user = User::where('id', $request->userId)->firstOrFail();

        $lock = $factory->createLock('call-request-' . $user->id);

        if ($lock->acquire()) {
            $user->call_date = Carbon::now();
            $user->call_count = !empty($user->call_count) ? $user->call_count + 1 : 1;
            $user->save();

            // Change Hubpost Call field
            try {
                $hubspot = app(Hubspot::class);
                $contact = $hubspot->setUserCallDate($user);
            } catch (Exception $e) {
                $lock->release();
                return response()->json('Could not update call date.', 401);
            }

            $lock->release();
        }
    }

    /**
     * Send Accreditation Email to User.
     *
     * @param User $user
     * @return void
     */
    public function sendAccreditationEmail($user)
    {
        $data = [
            'date' => $user->accreditated_at->format('m-d-Y'),
            'name' => sprintf('%s %s', $user->first_name, $user->last_name),
        ];

        Mail::to($user)->send(new AccreditationEmail($data));
    }

    /**
     * Send a reset link to the given user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function sendResetLinkEmail(Request $request)
    {

        $this->validateEmail($request);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $response = $this->broker()->sendResetLink(
            $request->only('email')
        );

        return $response == Password::RESET_LINK_SENT
            ? response()->json('Success')
            : response()->json('Error', 404);
    }

    protected function stringOrNull($value) {
        return !empty($value) ? $value : NULL;
    }

    private function storeDeviceVersion(string $userId, string $token, string $udid, string $version)
    {
        Device::with(['pushNotificationToken'])->updateOrCreate([
            'udid'        => $udid,
        ], [
            'udid'        => $udid,
            'app_version' => $version,
            'user_id'     => $userId,
            'token'       => $token
        ]);
    }
}
