<?php

namespace App\Http\Controllers\Api\V3\Account\Point\XoxoVoucher;

use App;
use App\Actions\Models\Point\RemovePointsOnRedeemed;
use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V3\Account\Point\XoxoVoucher\Redeem\StoreRequest;
use App\Http\Resources\Api\V3\Account\Point\Redemption\BaseResource;
use App\Models\Phone;
use App\Models\XoxoRedemption;
use App\Models\XoxoVoucher;
use App\Services\Xoxo\Xoxo;
use Auth;
use DB;

class RedeemController extends Controller
{
    /**
     * @throws \Throwable
     */
    public function store(StoreRequest $request, XoxoVoucher $xoxoVoucher)
    {
        $user         = Auth::user();
        $denomination = $request->get(RequestKeys::DENOMINATION);

        $xoxoRedemption = DB::transaction(function() use ($user, $xoxoVoucher, $denomination) {

            $xoxoRedemption = XoxoRedemption::create([
                'redemption_code'    => 0,
                'voucher_code'       => $xoxoVoucher->code,
                'name'               => $xoxoVoucher->name,
                'image'              => $xoxoVoucher->image,
                'description'        => $xoxoVoucher->description,
                'instructions'       => $xoxoVoucher->instructions,
                'terms_conditions'   => $xoxoVoucher->terms_conditions,
                'value_denomination' => 0,
                'amount_charged'     => 0,
            ]);

            $defaultQuantity = 1;
            /** @var Phone $phone */
            $phone      = $user->phone()->first();
            $fullNumber = $phone ? '+' . $phone->country_code . '-' . $phone->number : null;
            $xoxo       = App::make(Xoxo::class);
            $response   = $xoxo->redeem($xoxoVoucher->code, $defaultQuantity, $denomination,
                $xoxoRedemption->getRouteKey(), $user->email, $fullNumber);

            $xoxoRedemption->redemption_code    = $response['orderId'];
            $xoxoRedemption->value_denomination = (int) $denomination;
            $xoxoRedemption->amount_charged     = $response['amountCharged'];
            $xoxoRedemption->save();

            App::make(RemovePointsOnRedeemed::class, ['user' => $user, 'xoxoRedemption' => $xoxoRedemption])->execute();

            return $xoxoRedemption;
        });

        return new BaseResource($xoxoRedemption);
    }
}

