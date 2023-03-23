<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Config;
use Database\Factories\PhoneFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Passport\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * @method static PhoneFactory factory()
 *
 * @mixin Phone
 */
class Phone extends Authenticatable implements JWTSubject
{
    use Notifiable;
    use HasApiTokens;

    /* |--- CONSTANTS ---| */
    const MORPH_ALIAS = 'phone';
    /* |--- GLOBAL VARIABLES ---| */

    protected $casts = [
        'id'      => 'integer',
        'user_id' => 'integer',
    ];
    protected $dates = [
        'verified_at',
        'created_at',
        'updated_at',
    ];

    /* |--- FUNCTIONS ---| */

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [];
    }

    public function routeNotificationForSms(): string
    {
        return $this->country_code . $this->number;
    }

    public function fullNumber(): string
    {
        return $this->country_code . $this->number;
    }

    public function nextRequestAvailableAt(): CarbonInterface
    {
        if ($this->isVerified() && !$this->isAssigned()) {
            $ttl = Config::get('communications.phone.verification.ttl');

            return $this->created_at->clone()->addMinutes($ttl);
        }

        $authenticationCodes = $this->authenticationCodes()->get();
        if ($authenticationCodes->isEmpty()) {
            return Carbon::now();
        }

        $retryAfters         = Config::get('communications.sms.code.retry_after');
        $resetAfter          = Config::get('communications.sms.code.reset_after');
        $hasRetriesAvailable = $authenticationCodes->count() <= count($retryAfters ?? []);
        $authenticationCode  = $hasRetriesAvailable ? $authenticationCodes->last() : $authenticationCodes->first();
        $retryAfter          = $hasRetriesAvailable ? ($retryAfters[$authenticationCodes->count() - 1] ?? $resetAfter) : $resetAfter;

        return $authenticationCode->created_at->addSeconds($retryAfter);
    }

    public function isVerified(): bool
    {
        return !!$this->verified_at;
    }

    public function isVerifiedAndAssigned(): bool
    {
        return $this->isVerified() && $this->isAssigned();
    }

    public function isAssigned(): bool
    {
        return !!$this->user_id;
    }

    public function verify(): self
    {
        $this->verified_at = Carbon::now();

        return $this;
    }

    /* |--- RELATIONS ---| */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function authenticationCodes(): HasMany
    {
        return $this->hasMany(AuthenticationCode::class);
    }

    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
