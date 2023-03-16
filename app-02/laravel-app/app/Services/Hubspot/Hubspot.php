<?php

namespace App\Services\Hubspot;

use Config;
use App\Constants\MediaCollectionNames;
use App\Models\CompanyUser;
use App\Models\Phone;
use App\Models\Scopes\ByUserId;
use App\Models\Supplier;
use App\Models\User;
use App\Types\CompanyDataType;
use Exception;
use HubSpot\Client\Crm\Companies\ApiException as CompaniesApiException;
use HubSpot\Client\Crm\Companies\Model\PublicObjectSearchRequest;
use HubSpot\Client\Crm\Companies\Model\SimplePublicObjectInput as CompaniesSimplePublicObjectInput;
use HubSpot\Client\Crm\Contacts\ApiException as ContactsApiException;
use HubSpot\Client\Crm\Contacts\Model\SimplePublicObjectInput;
use HubSpot\Discovery\Discovery;
use HubSpot\Factory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;
use MenaraSolutions\Geographer\State;

class Hubspot
{
    protected Discovery $hubspot;

    public function __construct()
    {
        $this->hubspot = Factory::createWithAccessToken(Config::get('hubspot.access_token'));
    }

    public function createContact($user)
    {
        $contactInput = $this->fillContactInput($user);

        try {
            return $this->hubspot->crm()->contacts()->basicApi()->create($contactInput);
        } catch (ContactsApiException $exception) {
            return null;
        }
    }

    public function upsertContact($user)
    {
        $contactInput = $this->fillContactInput($user);
        $contactId    = $this->getContactId($user->email);

        try {
            if ($contactId) {
                $contact = $this->hubspot->crm()->contacts()->basicApi()->update($contactId, $contactInput);
            } else {
                $contact = $this->hubspot->crm()->contacts()->basicApi()->create($contactInput);
            }

            return $contact;
        } catch (ContactsApiException $exception) {
            return null;
        }
    }

    public function createCompany($user, $contactId)
    {
        if (empty($user->company) || empty($contactId)) {
            return null;
        }

        $companyInput    = new CompaniesSimplePublicObjectInput([
            'properties' => [
                'name'   => null,
                'domain' => null,
            ],
        ]);
        $industryMap     = [
            'commercial'                 => 'We do mostly Commercial',
            'residential'                => 'We do mostly Residential',
            'commercial-and-residential' => 'commercial-and-residential',
            'other'                      => 'Other',
        ];
        $serviceAppsUsed = !empty($user->apps) && is_array($user->apps) ? implode(';', $user->apps) : '';

        $companyInput->setProperties([
            'name'                          => $user->company,
            'industry'                      => $user->type_of_services ? $industryMap[$user->type_of_services] : '',
            'primary_focus'                 => $user->type_of_services ? ucwords($user->type_of_services) : '',
            'service_apps_used'             => $serviceAppsUsed,
            'number_of_techs'               => $user->employees,
            'have_they_been_group_trained_' => !!$user->group_code,
        ]);

        try {
            return $this->hubspot->crm()->companies()->basicApi()->create($companyInput);
        } catch (CompaniesApiException $exception) {
            return null;
        }
    }

    public function upsertCompany(Supplier $supplier)
    {
        $supplier->load(['contact', 'accountant', 'manager', 'counters']);

        $companyInput = new CompaniesSimplePublicObjectInput();
        $counters     = $supplier->counters()->take(10)->get();
        $image        = $supplier->getFirstMedia(MediaCollectionNames::IMAGES);
        $logo         = $supplier->getFirstMedia(MediaCollectionNames::LOGO);
        $properties   = [];
        $supplierId   = $supplier->getKey();

        foreach ($counters as $key => $counter) {
            $counterNumber                                      = $key + 1;
            $properties["counter_staff_{$counterNumber}_email"] = $counter->email;
            $properties["counter_staff_{$counterNumber}_name"]  = $counter->name;
        }

        $properties["do_you_offer_job_site_delivery_"] = $supplier->offers_delivery;
        $properties["store_address"]                   = $supplier->address;
        $properties["store_address_line_2"]            = "$supplier->address $supplier->address_2";
        $properties["store_contact_phone_number"]      = $supplier->contact_phone;
        $properties["store_contact_primary_email"]     = $supplier->contact_email;
        $properties["store_contact_secondary_email"]   = $supplier->contact_secondary_email;
        $properties["store_country"]                   = $supplier->country;
        $properties["store_email"]                     = $supplier->email;
        $properties["store_image"]                     = $image ? $image->getFullUrl() : null;
        $properties["store_name"]                      = $supplier->name;
        $properties["store_phone_number"]              = $supplier->phone;
        $properties["store_postal_code"]               = $supplier->zip_code;
        $properties["store_prokeep_phone_number"]      = $supplier->prokeep_phone;
        $properties["store_state"]                     = $supplier->getStateShortCode();
        $properties["unique_branch_identifier"]        = $supplier->branch;
        $properties["accounting_contact_email"]        = $supplier->accountant ? $supplier->accountant->email : null;
        $properties["accounting_contact_name"]         = $supplier->accountant ? $supplier->accountant->name : null;
        $properties["accounting_contact_phone_number"] = $supplier->accountant ? $supplier->accountant->phone : null;
        $properties["branch_manager_email"]            = $supplier->manager ? $supplier->manager->email : null;
        $properties["branch_manager_name"]             = $supplier->manager ? $supplier->manager->name : null;
        $properties["branch_manager_phone_number"]     = $supplier->manager ? $supplier->manager->phone : null;
        $properties["brands_carried_at_store"]         = $supplier->brands->pluck('name')->implode(', ');
        $properties["company_store_logo"]              = $logo ? $logo->getFullUrl() : null;
        $properties["unique_store_id"]                 = $supplierId;

        $companyInput->setProperties($properties);

        $companySearchRequest = new PublicObjectSearchRequest([
            "filter_groups" => [
                [
                    "filters" => [
                        [
                            "value"        => $supplier->getKey(),
                            "propertyName" => "unique_store_id",
                            "operator"     => "EQ",
                        ],
                    ],
                ],
            ],
            "limit"         => 1,
        ]);

        try {
            $companySearchResponse = $this->hubspot->crm()->companies()->searchApi()->doSearch($companySearchRequest);
            $companySearchResults  = $companySearchResponse->getResults();

            if (count($companySearchResults) > 0) {
                $companyId = $companySearchResults[0]['id'];
                $company   = $this->hubspot->crm()->companies()->basicApi()->update($companyId, $companyInput);
            } else {
                $company = $this->hubspot->crm()->companies()->basicApi()->create($companyInput);
            }

            $supplier->hubspot_id = $company->getId();
            $supplier->saveQuietly();

            return $company;
        } catch (CompaniesApiException $exception) {
            return null;
        }
    }

    public function updateCompanySupplierId(int $supplierId, string $companyId)
    {
        $companyInput = new CompaniesSimplePublicObjectInput([
            'properties' => [
                'unique_store_id' => $supplierId,
            ],
        ]);

        try {
            $this->hubspot->crm()->companies()->basicApi()->update($companyId, $companyInput);
        } catch (CompaniesApiException $exception) {
            // Silently ignored
        }
    }

    public function associateCompanyContact($companyId, $contactId)
    {
        try {
            $this->hubspot->crm()->companies()->AssociationsApi()->create($companyId, 'contact', $contactId, 2);
        } catch (CompaniesApiException $exception) {
            // Silently ignored
        }
    }

    public function setUserAccreditation($user)
    {
        $data = [
            'accredited'           => $user->accreditated ? 'Yes' : 'No',
            'accreditation_source' => 'App Accreditation',
        ];

        return $this->updateContact($user, $data);
    }

    public function setUserRegistration($user)
    {
        $accessCodeDate = $user->registration_completed_at->startOfDay()->getPreciseTimestamp(3);
        $data           = [
            'active'                             => $user->registration_completed ? 'Yes' : 'No',
            'accreditation_onboarding_code_used' => $user->access_code,
            'access_code_date'                   => $accessCodeDate,
        ];

        return $this->updateContact($user, $data);
    }

    public function setUserCallDate($user)
    {
        $data = [
            'onboarding_call_date_stamp' => $user->call_date->startOfDay()->getPreciseTimestamp(3),
        ];

        return $this->updateContact($user, $data);
    }

    public function updateUserSupportCallCount($user)
    {
        $data = [
            'calls_counter' => $user->calls_count ?: 0,
        ];

        return $this->updateContact($user, $data);
    }

    private function getContactId(string $email): ?string
    {
        if (!$email) {
            return null;
        }

        try {
            $contact = $this->hubspot->crm()->contacts()->basicApi()->getById($email, null, null, false, 'email');

            return $contact->getId();
        } catch (ContactsApiException $exception) {
            return null;
        }
    }

    public function updateUserSuppliers(User $user, Collection $suppliers)
    {
        $names = $suppliers->pluck(Supplier::keyName())->implode(',');
        $data  = [
            'local_suppliers_selected'           => $names,
            'number_of_local_suppliers_selected' => $suppliers->count(),
        ];

        return $this->updateContact($user, $data);
    }

    public function updateUserCompany(CompanyUser $companyUser)
    {
        $company          = $companyUser->company;
        $companyTypeMap   = [
            CompanyDataType::TYPE_CONTRACTOR             => 'Contractor',
            CompanyDataType::TYPE_SUPPLY_HOUSE           => 'Supplier',
            CompanyDataType::TYPE_TRADE_SCHOOL           => 'Trade School',
            CompanyDataType::TYPE_OEM                    => 'OEM',
            CompanyDataType::TYPE_PROPERTY_MANAGER_OWNER => 'AM/Owner',
        ];
        $equipmentType    = $companyUser->equipment_type;
        $equipmentTypeMap = [
            CompanyDataType::EQUIPMENT_TYPE_RESIDENTIAL            => 'Residential',
            CompanyDataType::EQUIPMENT_TYPE_COMMERCIAL             => 'Commercial',
            CompanyDataType::EQUIPMENT_TYPE_RESIDENTIAL_COMMERCIAL => 'Residential/Light Commercial',
            CompanyDataType::EQUIPMENT_TYPE_INDUSTRIAL             => 'Industrial',
        ];
        $user             = $companyUser->user;

        try {
            $state = State::build($company->state)->getName();
        } catch (Exception $exception) {
            $state = '';
        }

        $data = [
            'company_name_ob_temporary_'           => $company->name,
            'company_type_internal_use_new_'       => $company->type ? $companyTypeMap[$company->type] : '',
            'jobtitle'                             => $companyUser->job_title,
            'company_country__ob_'                 => $company->country,
            'company_postal_code__ob_'             => $company->zip_code,
            'company_state__ob_'                   => $state,
            'company_city__ob_'                    => $company->city,
            'commercial_or_residential_temporary_' => $equipmentType ? $equipmentTypeMap[$equipmentType] : '',
        ];

        return $this->updateContact($user, $data);
    }

    private function updateContact($user, $data)
    {
        if (!($contactId = $this->getContactId($user->email))) {
            return null;
        }

        $contactInput = new SimplePublicObjectInput();
        $contactInput->setProperties($data);

        try {
            return $this->hubspot->crm()->contacts()->basicApi()->update($contactId, $contactInput);
        } catch (ContactsApiException $exception) {
            return null;
        }
    }

    private function fillContactInput($user): SimplePublicObjectInput
    {
        $contactInput             = new SimplePublicObjectInput();
        $phone                    = Phone::scoped(new ByUserId($user->getKey()))->first();
        $updatedAt                = !empty($user->updated_at) ? $user->updated_at : $user->created_at;
        $whichServiceAppsDoYouUse = !empty($user->apps) && is_array($user->apps) ? implode(';', $user->apps) : '';

        $contactInput->setProperties([
            'firstname'                     => $user->first_name,
            'lastname'                      => $user->last_name,
            'email'                         => $user->email,
            'preferred_supplier_other_'     => $user->hvac_supplier,
            'which_service_apps_do_you_use' => $whichServiceAppsDoYouUse,
            'service_manager_name'          => $user->service_manager_name,
            'service_manager_phone_number'  => $user->service_manager_phone,
            'phone'                         => $phone ? $phone->number : null,
            'address'                       => $user->address . ' ' . $user->address_2,
            'city'                          => $user->city,
            'state'                         => $user->getStateShortCode(),
            'zip'                           => $user->zip,
            'country'                       => $user->country,
            'accredited'                    => $user->accreditated ? 'Yes' : 'No',
            'accreditation_source'          => $user->accreditated ? ($user->group_code ? 'Tech Group Training' : 'App Accreditation') : '',
            'group_trained_'                => !!$user->group_code,
            'calls_counter'                 => $user->calls_count,
            'manuals_counter'               => $user->manuals_count,
            'account_creation_date'         => $user->created_at->startOfDay()->getPreciseTimestamp(3),
            'bio'                           => $user->bio,
            'avatar'                        => !empty($user->photo) ? asset(Storage::url($user->photo)) : '',
            'job_title__profile_'           => $user->job_title,
            'union__profile_'               => $user->union,
            'years_experience'              => $user->experience_years,
            'company__profile_'             => $user->company,
            'profile_last_updated'          => $updatedAt->startOfDay()->getPreciseTimestamp(3),
            'verified'                      => $user->verified_at ? 'Yes' : 'No',
            'sent_hat_'                     => $user->hat_requested ? 'Needs to be Sent' : 'Does NOT Want Sent',
        ]);

        return $contactInput;
    }
}
