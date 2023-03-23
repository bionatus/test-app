<?php

namespace App\Actions\Models\Point;

use App;
use App\Models\AppSetting;
use App\Models\Point;
use App\Models\Scopes\ByRouteKey;
use App\Models\User;
use App\Models\XoxoRedemption;

class RemovePointsOnRedeemed
{
    private XoxoRedemption $xoxoRedemption;
    private User           $user;

    public function __construct(User $user, XoxoRedemption $xoxoRedemption)
    {
        $this->user           = $user;
        $this->xoxoRedemption = $xoxoRedemption;
    }

    public function execute(): void
    {
        $coefficient    = $this->user->currentLevel()->coefficient;
        $xoxoRedemption = $this->xoxoRedemption;
        $pointsRedeemed = (int) ceil($xoxoRedemption->value_denomination / Point::CASH_VALUE);

        /** @var AppSetting $multiplierSetting */
        $multiplierSetting = AppSetting::scoped(new ByRouteKey(AppSetting::SLUG_BLUON_POINTS_MULTIPLIER))->first();

        $multiplier = $multiplierSetting->value;

        $this->user->points()->create([
            'object_id'       => $xoxoRedemption->getKey(),
            'object_type'     => XoxoRedemption::MORPH_ALIAS,
            'action'          => Point::ACTION_REDEEMED,
            'coefficient'     => $coefficient,
            'multiplier'      => $multiplier,
            'points_redeemed' => $pointsRedeemed,
        ]);

        $this->user->processLevel();
    }
}
