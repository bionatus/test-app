<?php

namespace App\Http\Controllers\Api\V4\Account;

use App\Constants\MediaCollectionNames;
use App\Constants\RequestKeys;
use App\Events\User\CompanyUpdated;
use App\Events\User\HatRequested;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V4\Account\Profile\UpdateRequest;
use App\Http\Resources\Api\V4\Account\Profile\BaseResource;
use App\Models\Company;
use App\Models\Scopes\ByRouteKey;
use Auth;
use Exception;
use Illuminate\Support\Collection;
use Storage;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();

        return new BaseResource($user);
    }

    public function update(UpdateRequest $request)
    {
        $user = Auth::user();

        if ($request->hasFile(RequestKeys::PHOTO)) {
            try {
                $user->clearMediaCollection(MediaCollectionNames::IMAGES);
                $user->addMediaFromRequest(RequestKeys::PHOTO)
                    ->preservingOriginal()
                    ->toMediaCollection(MediaCollectionNames::IMAGES);

                $previousPhoto = $user->photo;
                $filename      = md5('profile-image' . time()) . '.jpg';
                Storage::disk('public')->putFileAs('', $request->file(RequestKeys::PHOTO), $filename);
                $user->photo = $filename;
                if ($previousPhoto) {
                    Storage::disk('public')->delete($previousPhoto);
                }
            } catch (Exception $exception) {
                // Silently ignored
            }
        }

        $validated = Collection::make($request->validated());

        $mapUserAttributes = Collection::make([
            RequestKeys::FIRST_NAME  => 'first_name',
            RequestKeys::LAST_NAME   => 'last_name',
            RequestKeys::EXPERIENCE  => 'experience_years',
            RequestKeys::PUBLIC_NAME => 'public_name',
            RequestKeys::BIO         => 'bio',
            RequestKeys::ADDRESS     => 'address',
            RequestKeys::ADDRESS_2   => 'address_2',
            RequestKeys::COUNTRY     => 'country',
            RequestKeys::STATE       => 'state',
            RequestKeys::CITY        => 'city',
            RequestKeys::ZIP_CODE    => 'zip',
        ]);

        if (is_null($user->hat_requested) && $validated->has(RequestKeys::HAT_REQUESTED)) {
            if ($user->hat_requested = !!$validated->get(RequestKeys::HAT_REQUESTED)) {
                HatRequested::dispatch($user);
            }
        }

        $mapUserAttributes->each(function(string $attribute, string $requestKey) use ($user, $validated) {
            if ($validated->has($requestKey)) {
                $user->setAttribute($attribute, $validated->get($requestKey));
            }
        });

        $companyRouteKey = $request->get(RequestKeys::COMPANY);
        if ($companyRouteKey) {

            $newCompanyParam             = Company::scoped(new ByRouteKey($companyRouteKey))->first();

            $companyUser                 = $user->companyUser;
            $companyUser->company_id     = $newCompanyParam->id;
            $companyUser->job_title      = $request->get(RequestKeys::JOB_TITLE);
            $companyUser->equipment_type = $request->get(RequestKeys::PRIMARY_EQUIPMENT_TYPE);

            $companyUser->save();
            $user->load('companyUser.company');
            CompanyUpdated::dispatch($companyUser);
        }

        $user->save();

        return new BaseResource($user);
    }
}
