<?php

namespace App\Http\Controllers\Api\V2\Twilio;

use App\Constants\OperatingSystems;
use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V2\Twilio\Token\StoreRequest;
use App\Http\Resources\Api\V2\Twilio\Token\BaseResource;
use Auth;
use Config;
use Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Twilio\Jwt\AccessToken;
use Twilio\Jwt\Grants\VoiceGrant;

class TokenController extends Controller
{
    const ACCESS_TOKEN_TTL = 36000;

    public function store(StoreRequest $request)
    {
        $token = new AccessToken(Config::get('twilio.account_sid'), Config::get('twilio.api_key'),
            Config::get('twilio.api_secret'), self::ACCESS_TOKEN_TTL, Auth::id());

        $voiceGrant = new VoiceGrant();
        $voiceGrant->setOutgoingApplicationSid(Config::get('twilio.app_sid'));

        switch ($request->get(RequestKeys::OS)) {
            case OperatingSystems::ANDROID:
                if ($pushSid = Config::get('twilio.android_push_credential_sid')) {
                    $voiceGrant->setPushCredentialSid($pushSid);
                }
                break;

            case OperatingSystems::IOS:
                if ($pushSid = Config::get('twilio.ios_push_credential_sid')) {
                    $voiceGrant->setPushCredentialSid($pushSid);
                }
                break;
        }

        $voiceGrant->setIncomingAllow(true);

        $token->addGrant($voiceGrant);

        return Response::make(new BaseResource($token), HttpResponse::HTTP_CREATED);
    }
}
