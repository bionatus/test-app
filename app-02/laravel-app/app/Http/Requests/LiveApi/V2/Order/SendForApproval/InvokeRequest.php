<?php

namespace App\Http\Requests\LiveApi\V2\Order\SendForApproval;

use App;
use App\Actions\Models\Setting\GetSupplierSetting;
use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;
use App\Models\Setting;
use App\Rules\MoneyFormat;
use Auth;
use Illuminate\Support\Arr;

class InvokeRequest extends FormRequest
{
    public function rules(): array
    {
        $supplier    = Auth::user()->supplier;
        $bidRequired = App::make(GetSupplierSetting::class,
            ['model' => $supplier, 'slug' => Setting::SLUG_BID_NUMBER_REQUIRED])->execute();

        $rules = ['nullable', 'bail', 'string', 'max:24'];

        if ($bidRequired) {
            unset($rules[0]);
            $rules = Arr::prepend($rules, 'required');
        }

        return [
            RequestKeys::TOTAL      => ['required', 'numeric', 'min:0', new MoneyFormat()],
            RequestKeys::BID_NUMBER => $rules,
            RequestKeys::NOTE       => ['nullable', 'string', 'max:255'],
        ];
    }
}
