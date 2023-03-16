<?php

namespace App\Actions\Models\Phone;

use App\Constants\RequestKeys;
use App\Constants\TwilioErrors;
use App\Events\AuthenticationCode\SmsRequested;
use App\Models\AuthenticationCode;
use App\Models\Phone;
use App\Models\Scopes\ByCreatedBefore;
use Config;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Spatie\QueueableAction\QueueableAction;
use Twilio\Exceptions\RestException;

class SendSMSRequest
{
    use QueueableAction;

    private string $authenticationCodeType;
    private string $message;
    private Phone  $phone;
    private array $authenticationCodeTypes = [
        AuthenticationCode::TYPE_VERIFICATION,
        AuthenticationCode::TYPE_LOGIN,
    ];

    public function __construct(Phone $phone, string $authenticationCodeType, string $message)
    {
        if (!in_array($authenticationCodeType, $this->authenticationCodeTypes)) {
            abort(500, 'Invalid authentication code type');
        }

        $this->authenticationCodeType = $authenticationCodeType;
        $this->message                = $message;
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

        $authenticationCode = $this->phone->authenticationCodes()->create([
            'type' => $this->authenticationCodeType,
        ]);

        try {
            SmsRequested::dispatch($authenticationCode);
        } catch (RestException $exception) {
            if (TwilioErrors::INVALID_TO_PHONE_NUMBER == $exception->getCode()) {
                throw ValidationException::withMessages([
                    RequestKeys::PHONE => $this->message,
                ]);
            }
        }
    }
}
