<?php

namespace App\Actions\Models\Phone;

use App\Constants\RequestKeys;
use App\Constants\TwilioErrors;
use App\Events\AuthenticationCode\CallRequested;
use App\Models\AuthenticationCode;
use App\Models\Phone;
use App\Models\Scopes\ByCreatedBefore;
use Config;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Spatie\QueueableAction\QueueableAction;
use Twilio\Exceptions\RestException;

class SendCallRequest
{
    use QueueableAction;

    private string $authenticationCodeType;
    private Phone  $phone;
    private array $authenticationCodeTypes = [
        AuthenticationCode::TYPE_VERIFICATION,
        AuthenticationCode::TYPE_LOGIN,
    ];

    public function __construct(Phone $phone, string $authenticationCodeType)
    {
        if (!in_array($authenticationCodeType, $this->authenticationCodeTypes)) {
            abort(500, 'Invalid authentication code type');
        }

        $this->authenticationCodeType = $authenticationCodeType;
        $this->phone                  = $phone;
    }

    /**
     * @throws ValidationException
     * @noinspection PhpRedundantCatchClauseInspection
     */
    public function execute()
    {
        $expiration = Carbon::now()->subSeconds(Config::get('communications.sms.code.reset_after'));
        AuthenticationCode::scoped(new ByCreatedBefore($expiration))->delete();

        if ($this->phone->authenticationCodes->isEmpty()) {
            $this->phone->authenticationCodes()->create([
                'type' => $this->authenticationCodeType,
            ]);
        }

        try {
            CallRequested::dispatch($this->phone);
        } catch (RestException $exception) {
            switch ($exception->getCode()) {
                case TwilioErrors::CALL_GEO_PERMISSIONS_NOT_ENABLED:
                case TwilioErrors::FROM_PHONE_NUMBER_NOT_VERIFIED:

                    throw ValidationException::withMessages([
                        RequestKeys::PHONE => 'Calls are not possible for the provided phone.',
                    ]);

                default:
                    // Silently ignored
            }
        }
    }
}
