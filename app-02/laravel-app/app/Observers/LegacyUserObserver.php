<?php

namespace App\Observers;

use App\Constants\MediaCollectionNames;
use App\Models\User as LatamUser;
use App\Services\Hubspot\Hubspot;
use App\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

class LegacyUserObserver
{
    /**
     * Handle the user "created" event.
     *
     * @param User $user
     *
     * @return JsonResponse|void
     */
    public function created(User $user)
    {
        // Assign user role
        if ($user->role) {
            $user->syncRoles([$user->role]);
        }

        try {
            $hubspot = app(Hubspot::class);
            $contact = $hubspot->createContact($user);

            if ($contact && $contact->getId()) {
                $user->hubspot_id = $contact->getId();
                $user->unsetEventDispatcher();
                $user->save();

                $company = $hubspot->createCompany($user, $contact->getId());

                if ($company) {
                    $hubspot->associateCompanyContact($company->getId(), $contact->getId());
                }
            }
        } catch (Exception $e) {
            return response()->json('Could not create account.', 401);
        }

        if ($user->accreditated) {
            // Change Hubspot Accreditation field
            try {
                $hubspot = app(Hubspot::class);
                $hubspot->setUserAccreditation($user);
            } catch (Exception $e) {
                return response()->json('Could not update accreditation.', 401);
            }
        }

        $this->syncPhotoToMediaLibrary($user);
    }

    /**
     * Handle the user "updated" event.
     *
     * @param User $user
     *
     * @return JsonResponse|void
     */
    public function updated(User $user)
    {
        $dirty = $user->getDirty();

        if ($user->role) {
            $user->syncRoles([$user->role]);
        }

        try {
            $hubspot = app(Hubspot::class);
            $contact = $hubspot->upsertContact($user);

            if ($contact && $contact->getId()) {
                $user->hubspot_id = $contact->getId();
                $user->unsetEventDispatcher();
                $user->save();
            }
        } catch (Exception $e) {
            return response()->json('Could not create account.', 401);
        }

        if ($user->isDirty('accreditated') || $user->isDirty('accreditated_at')) {
            // Change Hubspot Accreditation field
            try {
                $hubspot = app(Hubspot::class);
                $hubspot->setUserAccreditation($user);
            } catch (Exception $e) {
                return response()->json('Could not update accreditation.', 401);
            }
        }

        if (array_key_exists('photo', $dirty)) {
            $this->syncPhotoToMediaLibrary($user);
        }
    }

    protected function syncPhotoToMediaLibrary(User $user)
    {
        if ($user->photo) {
            $latamUser = LatamUser::find($user->getKey());
            try {
                $latamUser->clearMediaCollection(MediaCollectionNames::IMAGES);
                $media = $latamUser->addMediaFromDisk($user->photo, 'public')
                    ->preservingOriginal()
                    ->toMediaCollection(MediaCollectionNames::IMAGES);

                $media->uuid = $media->uuid ?: (string) Str::uuid();
                if (!$media->order_column) {
                    $media->setHighestOrderNumber();
                }
                $media->save();
            } catch (FileDoesNotExist | FileIsTooBig $exception) {
            }
        }
    }
}
