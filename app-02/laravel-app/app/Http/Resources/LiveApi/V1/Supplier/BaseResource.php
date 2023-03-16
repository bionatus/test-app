<?php

namespace App\Http\Resources\LiveApi\V1\Supplier;

use App\Constants\MediaCollectionNames;
use App\Http\Resources\Models\ImageResource;
use App\Http\Resources\Models\SupplierHourResource;
use App\Http\Resources\Types\CountryResource;
use App\Http\Resources\Types\StateResource;
use App\Models\Media;
use App\Models\Supplier;
use Illuminate\Http\Resources\Json\JsonResource;
use MenaraSolutions\Geographer\Country;
use MenaraSolutions\Geographer\Exceptions\ObjectNotFoundException;
use MenaraSolutions\Geographer\State;

/** @property Supplier $resource */
class BaseResource extends JsonResource
{
    public function __construct(Supplier $resource)
    {
        parent::__construct($resource);
    }

    /** @noinspection PhpRedundantCatchClauseInspection */
    public function toArray($request): array
    {
        $countryResource = null;
        $stateResource   = null;
        try {
            $country         = Country::build($this->resource->country);
            $countryResource = new CountryResource($country);

            $rawState      = $this->resource->state;
            $state         = $country->getStates()->filter(fn(State $state) => $state->isoCode === $rawState)->first();
            $stateResource = $state ? new StateResource($state) : null;
        } catch (ObjectNotFoundException $exception) {
            // Silently ignored
        }

        $accountant = $this->resource->accountant;
        $manager    = $this->resource->manager;

        /** @var Media $image */
        $image = $this->resource->getFirstMedia(MediaCollectionNames::IMAGES);
        /** @var Media $logo */
        $logo = $this->resource->getFirstMedia(MediaCollectionNames::LOGO);

        $canUseCurriDelivery = $this->resource->isCurriDeliveryEnabled();

        return [
            'id'                      => $this->resource->getRouteKey(),
            'name'                    => $this->resource->name,
            'branch'                  => $this->resource->branch,
            'email'                   => $this->resource->email,
            'phone'                   => $this->resource->phone,
            'prokeep_phone'           => $this->resource->prokeep_phone,
            'address'                 => $this->resource->address,
            'address_2'               => $this->resource->address_2,
            'zip_code'                => $this->resource->zip_code,
            'city'                    => $this->resource->city,
            'state'                   => $stateResource,
            'country'                 => $countryResource,
            'timezone'                => $this->resource->timezone,
            'about'                   => $this->resource->about,
            'open_hours'              => SupplierHourResource::collection($this->resource->supplierHours),
            'verified_at'             => $this->resource->verified_at,
            'contact_phone'           => $this->resource->contact_phone,
            'contact_email'           => $this->resource->contact_email,
            'contact_secondary_email' => $this->resource->contact_secondary_email,
            'accountant_name'         => !$accountant ? null : $accountant->name,
            'accountant_email'        => !$accountant ? null : $accountant->email,
            'accountant_phone'        => !$accountant ? null : $accountant->phone,
            'manager_name'            => !$manager ? null : $manager->name,
            'manager_email'           => !$manager ? null : $manager->email,
            'manager_phone'           => !$manager ? null : $manager->phone,
            'counter_staff'           => CounterStaffResource::collection($this->resource->counters),
            'brands'                  => new BrandCollection($this->resource->brands),
            'welcome_displayed_at'    => $this->resource->welcome_displayed_at,
            'offers_delivery'         => !!$this->resource->offers_delivery,
            'image'                   => $image ? new ImageResource($image) : null,
            'logo'                    => $logo ? new ImageResource($logo) : null,
            'take_rate'               => round($this->resource->take_rate / 100, 2),
            'take_rate_until'         => $this->resource->take_rate_until,
            'can_use_curri_delivery'  => $canUseCurriDelivery,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'id'                      => ['type' => ['string']],
                'name'                    => ['type' => ['string', 'null']],
                'email'                   => ['type' => ['string', 'null']],
                'branch'                  => ['type' => ['string', 'integer', 'null']],
                'phone'                   => ['type' => ['string', 'null']],
                'prokeep_phone'           => ['type' => ['string', 'null']],
                'address'                 => ['type' => ['string', 'null']],
                'address_2'               => ['type' => ['string', 'null']],
                'zip_code'                => ['type' => ['string', 'null']],
                'city'                    => ['type' => ['string', 'null']],
                'state'                   => StateResource::jsonSchema(),
                'country'                 => CountryResource::jsonSchema(),
                'timezone'                => ['type' => ['string', 'null']],
                'about'                   => ['type' => ['string', 'null']],
                'open_hours'              => [
                    'type'  => ['array'],
                    'items' => SupplierHourResource::jsonSchema(),
                ],
                'verified_at'             => ['type' => ['string', 'null']],
                'contact_phone'           => ['type' => ['string', 'null']],
                'contact_email'           => ['type' => ['string', 'null']],
                'contact_secondary_email' => ['type' => ['string', 'null']],
                'accountant_name'         => ['type' => ['string', 'null']],
                'accountant_email'        => ['type' => ['string', 'null']],
                'accountant_phone'        => ['type' => ['string', 'null']],
                'manager_name'            => ['type' => ['string', 'null']],
                'manager_email'           => ['type' => ['string', 'null']],
                'manager_phone'           => ['type' => ['string', 'null']],
                'counter_staff'           => CounterStaffResource::jsonSchema(),
                'brands'                  => BrandCollection::jsonSchema(),
                'welcome_displayed_at'    => ['type' => ['string', 'null']],
                'offers_delivery'         => ['type' => ['boolean']],
                'image'                   => ImageResource::jsonSchema(true),
                'logo'                    => ImageResource::jsonSchema(true),
                'take_rate'               => ['type' => ['number']],
                'take_rate_until'         => ['type' => ['string']],
                'can_use_curri_delivery'  => ['type' => ['boolean']],
            ],
            'required'             => [
                'id',
                'name',
                'email',
                'branch',
                'phone',
                'prokeep_phone',
                'address',
                'address_2',
                'zip_code',
                'city',
                'state',
                'country',
                'timezone',
                'about',
                'open_hours',
                'verified_at',
                'contact_phone',
                'contact_email',
                'contact_secondary_email',
                'accountant_name',
                'accountant_email',
                'accountant_phone',
                'manager_name',
                'manager_email',
                'manager_phone',
                'counter_staff',
                'brands',
                'welcome_displayed_at',
                'offers_delivery',
                'image',
                'logo',
                'take_rate',
                'take_rate_until',
                'can_use_curri_delivery',
            ],
            'additionalProperties' => false,
        ];
    }
}
