<?php

namespace App\Nova\Resources;

use App\Constants\MediaCollectionNames;
use App\Constants\MediaConversionNames;
use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Events\User\CompanyUpdated;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\Phone;
use App\Models\User as UserModel;
use App\Nova\Resources\Point as PointNova;
use App\Nova\Resources\User\UserSupplier;
use App\Types\CompanyDataType;
use App\Types\CountryDataType;
use Config;
use Ebess\AdvancedNovaMediaLibrary\Fields\Images;
use Epartment\NovaDependencyContainer\HasDependencies;
use Epartment\NovaDependencyContainer\NovaDependencyContainer;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Lang;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Password;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;
use MenaraSolutions\Geographer\Country;
use MenaraSolutions\Geographer\Earth;
use Metrixinfo\Nova\Fields\Iframe\Iframe;
use NovaAjaxSelect\AjaxSelect;
use NovaButton\Button;
use Storage;
use Str;
use URL;

/** @mixin UserModel */
class User extends Resource
{
    use HasDependencies;

    public static $model           = UserModel::class;
    public static $title           = 'name';
    public static $search          = [
        'id',
        'name',
        'email',
    ];
    public static $searchRelations = [
        'phone' => [
            'number',
        ],
    ];
    public static $group           = 'Current';

    public static function uriKey()
    {
        return 'latam-' . parent::uriKey();
    }

    public function fields(Request $request)
    {
        return [
            ID::make()->sortable(),
            Text::make('First Name')->sortable()->rules('required', 'max:255'),
            Text::make('Last Name')->sortable()->rules('required', 'max:255'),
            Text::make('Email')
                ->sortable()
                ->rules('required', 'max:255', 'bail', 'email:strict', 'ends_with_tld')
                ->creationRules('unique:users,email')
                ->updateRules('unique:users,email,{{resourceId}}'),
            Password::make('Password')
                ->onlyOnForms()
                ->creationRules('required', 'string', 'min:6')
                ->updateRules('nullable', 'string', 'min:6'),
            Date::make('Created At')->hideWhenCreating()->hideWhenUpdating(),

            Images::make('Photo', MediaCollectionNames::IMAGES)
                ->croppable(false)
                ->conversionOnIndexView(MediaConversionNames::THUMB)
                ->conversionOnForm(MediaConversionNames::THUMB)
                ->conversionOnPreview(MediaConversionNames::THUMB)
                ->conversionOnDetailView(MediaConversionNames::THUMB)
                ->singleMediaRules(['mimes:jpg,jpeg,png,gif'])
                ->setMaxFileSize(Config::get('media-library.max_file_size') / 1024),

            Boolean::make('Verified')->resolveUsing(fn() => $this->isVerified())->readonly(),

            Boolean::make('Disabled', 'disabled_at')
                ->trueValue(Carbon::now())
                ->falseValue(null)
                ->hideFromIndex()
                ->withMeta(['value' => $this->isDisabled()]),

            new Panel('Company Information', $this->companyInformationFields($request)),

            new Panel('Mailing Address', $this->mailingAddressFields()),

            new Panel('Phone', $this->phoneFields()),
            HasMany::make('Points', 'points', PointNova::class)->hideFromIndex(),
            BelongsToMany::make('Suppliers', 'suppliers', UserSupplier::class)
                ->searchable()
                ->withSubtitles()
                ->fields(function($request, $relatedModel) {
                    return [
                        Boolean::make('Visible By User', 'visible_by_user')
                            ->withMeta(['value' => $relatedModel->pivot ? $relatedModel->pivot->visible_by_user : true]),
                    ];
                }),
            new Panel('Supplier Map', [
                Iframe::make(null, function() {
                    return \file_get_contents('https://tv-view-uphvr.ondigitalocean.app/memex.html');
                })
                    ->hideFromIndex()
                    ->size('100%', 550)
                    ->style('box-shadow: 0px 0px 5px 1px black; transition: box-shadow 3.25s ease-in-out; transform: translateX(-16%); '),
            ]),
            Button::make('Go To HubSpot Form', 'hubspot_form')
                ->link(Config::get('hubspot.form_url') . $this->email)
                ->style('primary')
                ->hideFromIndex(),
        ];
    }

    public function mailingAddressFields(): array
    {
        $countries = $this->getValidCountries();

        return [
            Text::make('Address')->hideFromIndex(),
            Text::make('Address 2')->hideFromIndex(),

            Select::make('Country')->hideFromIndex()->options($countries->pluck('name', 'code'))->nullable()->rules([
                'required_with:state',
                'nullable',
                Rule::in(CountryDataType::getAllowedCountries()),
            ]),
            AjaxSelect::make('State')->get($this->getStatesUrl('country'))->parent('country'),
            Text::make('State')->readonly()->onlyOnDetail()->displayUsing(fn() => $this->state ?? null),
            Text::make('Zip Code', 'zip')->hideFromIndex(),
            Text::make('City')->hideFromIndex(),

        ];
    }

    public function phoneFields(): array
    {
        return [
            Text::make('Country Code', RequestKeys::COUNTRY_CODE)->hideFromIndex()->resolveUsing(function() {
                return $this->phone()->first()->country_code ?? null;
            })->rules([
                'required_with:' . RequestKeys::PHONE,
            ])->fillUsing(fn() => ''),
            Text::make('Phone', RequestKeys::PHONE)->resolveUsing(function() {
                return $this->phone()->first()->number ?? null;
            })->rules([
                'required_with:' . RequestKeys::COUNTRY_CODE,
            ])->fillUsing(fn() => ''),
        ];
    }

    public function companyInformationFields(Request $request): array
    {
        $countries = $this->getValidCountries();

        return [
            Text::make('Name', RequestKeys::COMPANY_NAME)->resolveUsing(function() {
                return $this->companyUser->company->name ?? null;
            })->hideFromIndex()->rules([
                'required_with:' . implode(',', $this->companyFields(RequestKeys::COMPANY_NAME)),
            ])->fillUsing(fn() => ''),

            Select::make('Type', RequestKeys::COMPANY_TYPE)->resolveUsing(function() {
                return $this->companyUser->company->type ?? null;
            })->hideFromIndex()->options(array_combine(CompanyDataType::ALL_COMPANY_TYPES,
                CompanyDataType::ALL_COMPANY_TYPES))->rules([
                'required_with:' . implode(',', $this->companyFields(RequestKeys::COMPANY_TYPE)),
                'nullable',
                (string) Rule::in(CompanyDataType::ALL_COMPANY_TYPES),
            ])->nullable()->fillUsing(fn() => ''),

            Text::make('What equipment do you primarily work on?', 'display_' . RequestKeys::PRIMARY_EQUIPMENT_TYPE)
                ->displayUsing(fn() => $this->companyUser->equipment_type ?? null)
                ->onlyOnDetail()
                ->showOnDetail(fn(
                ) => ($this->companyUser->company->type ?? null) === CompanyDataType::TYPE_CONTRACTOR),

            NovaDependencyContainer::make([
                Select::make('What equipment do you primarily work on?', RequestKeys::PRIMARY_EQUIPMENT_TYPE)
                    ->resolveUsing(function() {
                        return $this->companyUser->equipment_type ?? null;
                    })
                    ->hideFromIndex()
                    ->options(array_combine(CompanyDataType::ALL_EQUIPMENT_TYPES, CompanyDataType::ALL_EQUIPMENT_TYPES))
                    ->rules([
                        'required_if:' . RequestKeys::COMPANY_TYPE . ',' . CompanyDataType::TYPE_CONTRACTOR,
                        Rule::in(CompanyDataType::ALL_EQUIPMENT_TYPES),
                    ])
                    ->fillUsing(fn() => ''),
            ])->dependsOn(RequestKeys::COMPANY_TYPE, CompanyDataType::TYPE_CONTRACTOR),

            AjaxSelect::make('Job Title', RequestKeys::JOB_TITLE)->resolveUsing(function() {
                return $this->companyUser->job_title ?? null;
            })->get($this->getJobTitlesUrl(RequestKeys::COMPANY_TYPE))->parent(RequestKeys::COMPANY_TYPE)->rules([
                'required_with:' . implode(',', $this->companyFields(RequestKeys::JOB_TITLE)),
                Rule::in($this->getValidJobTitles($request->get(RequestKeys::COMPANY_TYPE))),
            ])->showOnDetail(),

            Text::make('Job Title', 'display_' . RequestKeys::JOB_TITLE)->displayUsing(fn(
            ) => $this->companyUser->job_title ?? null)->onlyOnDetail(),

            Select::make('Country', RequestKeys::COMPANY_COUNTRY)->resolveUsing(function() {
                return $this->companyUser->company->country ?? null;
            })->hideFromIndex()->options($countries->pluck('name', 'code'))->nullable()->rules([
                'required_with:' . implode(',', $this->companyFields(RequestKeys::COMPANY_COUNTRY)),
                'nullable',
                Rule::in(CountryDataType::getAllowedCountries()),
            ])->fillUsing(fn() => ''),

            AjaxSelect::make('State', RequestKeys::COMPANY_STATE)
                ->resolveUsing(fn() => $this->companyUser->company->state ?? null)
                ->get($this->getStatesUrl(RequestKeys::COMPANY_COUNTRY))
                ->parent(RequestKeys::COMPANY_COUNTRY)
                ->rules([
                    'required_with:' . implode(',', $this->companyFields(RequestKeys::COMPANY_STATE)),
                ])
                ->fillUsing(fn() => ''),

            Text::make('State')->readonly()->onlyOnDetail()->displayUsing(fn(
            ) => $this->companyUser->company->state ?? null),

            Text::make('City', RequestKeys::COMPANY_CITY)->resolveUsing(function() {
                return $this->companyUser->company->city ?? null;
            })->hideFromIndex()->rules([
                'required_with:' . implode(',', $this->companyFields(RequestKeys::COMPANY_CITY)),
            ])->fillUsing(fn() => ''),

            Text::make('Address', RequestKeys::COMPANY_ADDRESS)->resolveUsing(function() {
                return $this->companyUser->company->address ?? null;
            })->hideFromIndex()->rules([
                'required_with:' . implode(',', $this->companyFields(RequestKeys::COMPANY_ADDRESS)),
            ])->fillUsing(fn() => ''),

            Text::make('Zip Code', RequestKeys::COMPANY_ZIP_CODE)->resolveUsing(function() {
                return $this->companyUser->company->zip_code ?? null;
            })->hideFromIndex()->rules([
                'required_with:' . implode(',', $this->companyFields(RequestKeys::COMPANY_ZIP_CODE)),
            ])->fillUsing(fn() => ''),
        ];
    }

    private function getValidCountries(): Collection
    {
        $geo = new Earth();

        return Collection::make($geo->getCountries()->useShortNames()->sortBy('name'))->filter(fn($country
        ) => in_array($country->code, CountryDataType::getAllowedCountries()))->map(fn(Country $country
        ) => ['name' => $country->getName(), 'code' => $country->code]);
    }

    private function getStatesUrl(string $parameter): string
    {
        return Str::replace('PLACEHOLDER', '{' . $parameter . '}',
            URL::route(RouteNames::API_NOVA_ADDRESS_COUNTRY_STATE_INDEX, [RouteParameters::COUNTRY => 'PLACEHOLDER']));
    }

    private function getJobTitlesUrl(string $parameter): string
    {
        return Str::replace('PLACEHOLDER', '{' . $parameter . '}',
            URL::route(RouteNames::API_NOVA_JOB_TITLE_INDEX, ['company_type' => 'PLACEHOLDER']));
    }

    private function getValidJobTitles($companyType): array
    {
        return is_string($companyType) ? CompanyDataType::getJobTitles($companyType) : [];
    }

    protected static function afterValidation(NovaRequest $request, $validator)
    {

        if ($countryCode = $request->get('country')) {
            try {
                $country    = Country::build($countryCode);
                $stateCodes = $country->getStates()->pluck('isoCode');

                $state = $request->get('state');
                if ($state && !in_array($state, $stateCodes)) {
                    $validator->errors()->add('state', Lang::get('validation.in', ['attribute' => 'State']));
                }
            } catch (Exception $exception) {
            }
        }

        if ($countryCode = $request->get(RequestKeys::COMPANY_COUNTRY)) {
            try {
                $country    = Country::build($countryCode);
                $stateCodes = $country->getStates()->pluck('isoCode');

                $state = $request->get(RequestKeys::COMPANY_STATE);
                if ($state && !in_array($state, $stateCodes)) {
                    $validator->errors()
                        ->add(RequestKeys::COMPANY_STATE, Lang::get('validation.in', ['attribute' => 'State']));
                }
            } catch (Exception $exception) {
            }
        }
    }

    public static function redirectAfterCreate(NovaRequest $request, $resource)
    {
        /** @var UserModel $user */
        $user = $resource->resource;

        self::updateOrCreateCompany($user, $request);

        self::updateOrCreatePhone($user, $request);

        return parent::redirectAfterCreate($request, $resource);
    }

    private static function updateOrCreateCompany(UserModel $user, NovaRequest $request)
    {
        $mapCompanyAttributes     = Collection::make([
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

        $companyRelatedAttributes = $mapCompanyAttributes->keys()->merge($mapCompanyUserAttributes->keys());

        $hasAnyCompanyRelatedAttributesFilled = !!$companyRelatedAttributes->filter(fn(string $attribute
        ) => !empty($request->get($attribute)))->count();

        if ($hasAnyCompanyRelatedAttributesFilled) {
            $company = $user->companyUser->company ?? new Company();

            $mapCompanyAttributes->each(function(string $attribute, string $requestKey) use ($request, $company) {
                if ($request->has($requestKey)) {
                    $company->setAttribute($attribute, $request->get($requestKey));
                }
            });
            $company->save();

            if (!($companyUser = $user->companyUser)) {
                $companyUser             = new CompanyUser();
                $companyUser->user_id    = $user->getKey();
                $companyUser->company_id = $company->getKey();
            }
            $mapCompanyUserAttributes->each(function(string $attribute, string $requestKey) use (
                $request,
                $companyUser
            ) {
                if ($request->has($requestKey)) {
                    $companyUser->setAttribute($attribute, $request->get($requestKey));
                }
            });
            $companyUser->save();

            CompanyUpdated::dispatch($companyUser);
        }
    }

    private static function updateOrCreatePhone(UserModel $user, NovaRequest $request)
    {
        $mapPhoneAttributes = Collection::make([
            RequestKeys::COUNTRY_CODE => 'country_code',
            RequestKeys::PHONE        => 'number',
        ]);

        $hasPhoneRelatedAttributesFilled = !!$mapPhoneAttributes->keys()->filter(fn(string $attribute
        ) => !empty($request->get($attribute)))->count();

        if (!$hasPhoneRelatedAttributesFilled) {
            return;
        }

        $phone              = $user->phone()->first() ?? new Phone(['user_id' => $user->id]);
        $phone->verified_at ??= Carbon::now();

        $mapPhoneAttributes->each(function(string $attribute, string $requestKey) use ($request, $phone) {
            if ($request->has($requestKey)) {
                $phone->setAttribute($attribute, $request->get($requestKey));
            }
        });

        $phone->save();
    }

    protected static function fillFields(NovaRequest $request, $model, $fields)
    {
        if (is_a($model, UserModel::class)) {
            /** @var UserModel $model */
            if ($model->exists) {
                self::updateOrCreateCompany($model, $request);
                self::updateOrCreatePhone($model, $request);
            }

            if ($request->hasFile("__media__.images.0")) {
                try {
                    /** @var UploadedFile $file */
                    $file          = $request->__media__['images'][0];
                    $fileExtension = $file->getClientOriginalExtension();
                    $filename      = md5('profile-image' . time()) . '.' . $fileExtension;
                    $previousPhoto = $model->photo;
                    Storage::disk('public')->putFileAs('', $file, $filename);
                    $model->photo = $filename;

                    if ($previousPhoto) {
                        Storage::disk('public')->delete($previousPhoto);
                    }
                } catch (Exception $exception) {
                    // Silently ignored
                }
            }
        }

        return parent::fillFields($request, $model, $fields);
    }

    private function companyFields(string $except = ''): array
    {
        return Collection::make([
            RequestKeys::COMPANY_NAME,
            RequestKeys::COMPANY_TYPE,
            RequestKeys::COMPANY_COUNTRY,
            RequestKeys::COMPANY_STATE,
            RequestKeys::COMPANY_CITY,
            RequestKeys::COMPANY_ZIP_CODE,
            RequestKeys::COMPANY_ADDRESS,
            RequestKeys::JOB_TITLE,
            RequestKeys::PRIMARY_EQUIPMENT_TYPE,
        ])->filter(fn($value) => $value != $except)->toArray();
    }
}
