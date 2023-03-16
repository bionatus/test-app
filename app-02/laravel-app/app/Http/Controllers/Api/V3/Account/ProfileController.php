<?php

namespace App\Http\Controllers\Api\V3\Account;

use App\Actions\Models\Activity\BuildResource;
use App\Constants\MediaCollectionNames;
use App\Constants\RequestKeys;
use App\Events\User\CompanyUpdated;
use App\Events\User\HatRequested;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V3\Account\Profile\UpdateRequest;
use App\Http\Resources\Api\V3\Account\Profile\BaseResource;
use App\Http\Resources\Api\V3\Activity\CompanyResource;
use App\Jobs\LogActivity;
use App\Models\Activity;
use App\Models\Company;
use App\Models\CompanyUser;
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

        $mapCompanyAttributes = Collection::make([
            RequestKeys::COMPANY_NAME     => 'name',
            RequestKeys::COMPANY_TYPE     => 'type',
            RequestKeys::COMPANY_COUNTRY  => 'country',
            RequestKeys::COMPANY_STATE    => 'state',
            RequestKeys::COMPANY_CITY     => 'city',
            RequestKeys::COMPANY_ZIP_CODE => 'zip_code',
            RequestKeys::COMPANY_ADDRESS  => 'address',
        ]);

        $mapCompanyUserAttributes = Collection::make([
            RequestKeys::JOB_TITLE              => 'job_title',
            RequestKeys::PRIMARY_EQUIPMENT_TYPE => 'equipment_type',
        ]);
        $companyRelatedAttributes = $mapCompanyAttributes->keys()->merge($mapCompanyUserAttributes->keys())->toArray();

        if ($request->hasAny($companyRelatedAttributes)) {
            $company = $user->companyUser->company ?? new Company();
            $mapCompanyAttributes->each(function(string $attribute, string $requestKey) use ($validated, $company) {
                if ($validated->has($requestKey)) {
                    $company->setAttribute($attribute, $validated->get($requestKey));
                }
            });
            $company->save();
            if ($company->wasChanged()) {
                $property = (new BuildResource($company, CompanyResource::class))->execute();
                LogActivity::dispatch(Activity::ACTION_UPDATED, Activity::RESOURCE_PROFILE, $company, Auth::getUser(),
                    $property, Activity::TYPE_PROFILE);
            }

            if (!($companyUser = $user->companyUser)) {
                $companyUser             = new CompanyUser();
                $companyUser->user_id    = $user->getKey();
                $companyUser->company_id = $company->getKey();
            }
            $mapCompanyUserAttributes->each(function(string $attribute, string $requestKey) use (
                $validated,
                $companyUser
            ) {
                if ($validated->has($requestKey)) {
                    $companyUser->setAttribute($attribute, $validated->get($requestKey));
                }
            });
            $companyUser->save();

            $user->load('companyUser.company');

            CompanyUpdated::dispatch($companyUser);
        }

        $user->save();

        return new BaseResource($user);
    }
}
