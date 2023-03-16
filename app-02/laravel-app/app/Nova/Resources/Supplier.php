<?php

namespace App\Nova\Resources;

use App\Constants\MediaCollectionNames;
use App\Constants\MediaConversionNames;
use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Constants\Timezones;
use App\Models\Scopes\ByType;
use App\Models\Staff;
use App\Models\Supplier as SupplierModel;
use App\Nova\Resources\Staff as StaffNova;
use App\Types\CountryDataType;
use Config;
use Ebess\AdvancedNovaMediaLibrary\Fields\Images;
use GeneaLabs\NovaMapMarkerField\MapMarker;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Password;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;
use MenaraSolutions\Geographer\Country;
use MenaraSolutions\Geographer\Earth;
use NovaAjaxSelect\AjaxSelect;
use Str;
use URL;

/** @mixin SupplierModel */
class Supplier extends Resource
{
    public static $model  = SupplierModel::class;
    public static $title  = 'name';
    public static $search = [
        'id',
        'airtable_id',
        'name',
        'email',
        'address',
        'city',
    ];
    public static $group  = 'Current';

    public function fields(Request $request)
    {
        $countries = $this->getValidCountries();

        return [
            ID::make()->sortable(),
            Text::make('Airtable ID')->onlyOnDetail()->readonly(),
            Text::make('Name')->sortable()->rules('required', 'max:255'),
            Images::make('Logo', MediaCollectionNames::LOGO)
                ->croppable(false)
                ->conversionOnIndexView(MediaConversionNames::THUMB)
                ->conversionOnForm(MediaConversionNames::THUMB)
                ->conversionOnPreview(MediaConversionNames::THUMB)
                ->conversionOnDetailView(MediaConversionNames::THUMB)
                ->singleMediaRules(['mimes:jpg,jpeg,png,gif'])
                ->setMaxFileSize(Config::get('media-library.max_file_size') / 1024),
            Images::make('Image', MediaCollectionNames::IMAGES)
                ->croppable(false)
                ->conversionOnIndexView(MediaConversionNames::THUMB)
                ->conversionOnForm(MediaConversionNames::THUMB)
                ->conversionOnPreview(MediaConversionNames::THUMB)
                ->conversionOnDetailView(MediaConversionNames::THUMB)
                ->singleMediaRules(['mimes:jpg,jpeg,png,gif'])
                ->setMaxFileSize(Config::get('media-library.max_file_size') / 1024)
                ->hideFromIndex(),
            Number::make('Unique Branch Identifier', 'branch')
                ->rules('nullable', 'integer', 'min:1', 'digits_between:1,8')
                ->hideFromIndex(),
            Text::make('Address'),
            Text::make('Address 2')->hideFromIndex(),
            Select::make('Timezone')->resolveUsing(function() {
                return $this->timezone ?? null;
            })->hideFromIndex()->options(Collection::make(Timezones::ALLOWED_TIMEZONES)->map(function($value) {
                return [
                    'label' => $value,
                    'value' => $value,
                ];
            })->values())->nullable()->rules('nullable', Rule::in(Timezones::ALLOWED_TIMEZONES),),
            Select::make('Country')->hideFromIndex()->options($countries->pluck('name', 'code'))->nullable()->rules([
                'required_with:state',
                'nullable',
                Rule::in(CountryDataType::getAllowedCountries()),
            ]),
            AjaxSelect::make('State')->hideFromIndex()->showOnDetail()->get($this->getStatesUrl())->parent('country'),
            Text::make('State', 'display_state')->readonly()->onlyOnDetail()->displayUsing(fn(
            ) => $this->state ?? null),
            Text::make('City'),
            Text::make('Zip Code')->hideFromIndex(),
            Text::make('Email')
                ->rules('required', 'max:255', 'bail', 'email:strict', 'ends_with_tld')
                ->creationRules('unique:suppliers,email')
                ->updateRules('unique:suppliers,email,{{resourceId}}'),
            Password::make('Password')
                ->onlyOnForms()
                ->creationRules('required', 'string', 'min:8')
                ->updateRules('nullable', 'string', 'min:8')
                ->fillUsing(fn() => ''),
            Text::make('Phone')->hideFromIndex(),
            Text::make('Prokeep Phone Number', 'prokeep_phone')->hideFromIndex(),
            Boolean::make('Offers Delivery')->hideFromIndex(),
            Boolean::make('Published', 'published_at')
                ->trueValue(Carbon::now())
                ->falseValue(null)
                ->hideFromIndex()
                ->withMeta(['value' => !!$this->published_at]),
            Number::make('Take Rate')->step(SupplierModel::STEP)->default(fn(
            ) => round(SupplierModel::DEFAULT_TAKE_RATE / 100, 2))->resolveUsing(fn($value
            ) => null !== $value ? round($value / 100, 2) : null)->fillUsing(function(
                NovaRequest $request,
                SupplierModel $model,
                string $attribute
            ) {
                $model->{$attribute} = $request->get($attribute) * 100;
            })->hideFromIndex()->rules(['required']),
            Date::make('Take Rate Until')->default(Carbon::create(SupplierModel::DEFAULT_YEAR,
                SupplierModel::DEFAULT_MONTH, SupplierModel::DEFAULT_DAY))->hideFromIndex()->rules(['required']),
            Text::make('Terms')
                ->rules('required', 'max:255')
                ->default(SupplierModel::DEFAULT_PAYMENT_TERMS)
                ->hideFromIndex(),
            BelongsTo::make('Supplier Company', 'supplierCompany')->searchable()->nullable()->hideFromIndex(),
            Textarea::make('About your store', 'about'),
            HasMany::make('Supplier Hours', 'supplierHours')->hideFromIndex(),
            HasMany::make('Counter Staff', 'counters', StaffNova::class)->hideFromIndex(),
            new Panel('Store Contact Info', $this->storeContactInfoFields()),
            new Panel('Branch Manager Info', $this->branchManagerInfoFields()),
            new Panel('Accounting Contact Info', $this->accountingContactInfoFields()),
            new Panel('Location', $this->locationFields()),
        ];
    }

    public function storeContactInfoFields(): array
    {
        return [
            Text::make('Store Phone Number', 'contact_phone')->hideFromIndex(),
            Text::make('Primary Store Email', 'contact_email')
                ->rules('nullable', 'max:255', 'bail', 'email:strict', 'ends_with_tld')
                ->hideFromIndex(),
            Text::make('Secondary Email', 'contact_secondary_email')->hideFromIndex(),
        ];
    }

    public function branchManagerInfoFields(): array
    {
        return [
            Text::make('Name', Staff::TYPE_MANAGER . '_name')
                ->resolveUsing(fn() => $this->manager->name ?? null)
                ->hideFromIndex()
                ->fillUsing(fn() => ''),
            Text::make('Phone Number', Staff::TYPE_MANAGER . '_phone')->resolveUsing(fn(
            ) => $this->manager->phone ?? null)->hideFromIndex()->fillUsing(fn() => ''),
            Text::make('Manager Email', Staff::TYPE_MANAGER . '_email')
                ->resolveUsing(fn() => $this->manager->email ?? null)
                ->rules('nullable', 'max:255', 'bail', 'email:strict', 'ends_with_tld')
                ->hideFromIndex()
                ->fillUsing(fn() => ''),
        ];
    }

    public function accountingContactInfoFields(): array
    {
        return [
            Text::make('Name', Staff::TYPE_ACCOUNTANT . '_name')
                ->resolveUsing(fn() => $this->accountant->name ?? null)
                ->hideFromIndex()
                ->fillUsing(fn() => ''),
            Text::make('Phone Number', Staff::TYPE_ACCOUNTANT . '_phone')->resolveUsing(fn(
            ) => $this->accountant->phone ?? null)->hideFromIndex()->fillUsing(fn() => ''),
            Text::make('Email', Staff::TYPE_ACCOUNTANT . '_email')
                ->resolveUsing(fn() => $this->accountant->email ?? null)
                ->rules('nullable', 'max:255', 'bail', 'email:strict', 'ends_with_tld')
                ->hideFromIndex()
                ->fillUsing(fn() => ''),
        ];
    }

    public static function redirectAfterCreate(NovaRequest $request, $resource)
    {
        /** @var SupplierModel $supplier */
        $supplier = $resource->resource;

        $supplier->staff()->create([
            'type'     => Staff::TYPE_OWNER,
            'email'    => $request->get('email'),
            'password' => Hash::make($request->get('password')),
        ]);
        self::updateOrCreateManager($supplier, $request);
        self::updateOrCreateAccountant($supplier, $request);

        return parent::redirectAfterCreate($request, $resource);
    }

    public function locationFields(): array
    {
        return [
            MapMarker::make('Location')->latitude('latitude')->longitude('longitude')->hideFromIndex(),
        ];
    }

    public function subtitle()
    {
        return implode(', ', array_filter([
            $this->id,
            $this->address,
            $this->city,
            $this->state,
            $this->zip_code,
            $this->country,
        ]));
    }

    protected static function fillFields(NovaRequest $request, $model, $fields)
    {
        $supplier = $model;
        /** @var SupplierModel $supplier */
        if ($supplier->exists) {
            $ownerFieldsToUpdate = ['email' => $request->get('email')];
            if ($password = $request->get('password')) {
                $ownerFieldsToUpdate['password'] = Hash::make($password);
            }
            $supplier->staff()->scoped(new ByType(Staff::TYPE_OWNER))->update($ownerFieldsToUpdate);
            self::updateOrCreateManager($supplier, $request);
            self::updateOrCreateAccountant($supplier, $request);
        }

        return parent::fillFields($request, $model, $fields);
    }

    private static function updateOrCreateManager(SupplierModel $supplier, NovaRequest $request)
    {
        $supplier->staff()->updateOrCreate(['type' => Staff::TYPE_MANAGER], [
            'name'     => $request->get(Staff::TYPE_MANAGER . '_name'),
            'email'    => $request->get(Staff::TYPE_MANAGER . '_email'),
            'phone'    => $request->get(Staff::TYPE_MANAGER . '_phone'),
            'password' => '',
        ]);
    }

    private static function updateOrCreateAccountant(SupplierModel $supplier, NovaRequest $request)
    {
        $supplier->staff()->updateOrCreate(['type' => Staff::TYPE_ACCOUNTANT], [
            'name'     => $request->get(Staff::TYPE_ACCOUNTANT . '_name'),
            'email'    => $request->get(Staff::TYPE_ACCOUNTANT . '_email'),
            'phone'    => $request->get(Staff::TYPE_ACCOUNTANT . '_phone'),
            'password' => '',
        ]);
    }

    private function getValidCountries(): Collection
    {
        $geo = new Earth();

        return Collection::make($geo->getCountries()->useShortNames()->sortBy('name'))->filter(fn($country
        ) => in_array($country->code, CountryDataType::getAllowedCountries()))->map(fn(Country $country
        ) => ['name' => $country->getName(), 'code' => $country->code]);
    }

    private function getStatesUrl(): string
    {
        return Str::replace('PLACEHOLDER', '{country}',
            URL::route(RouteNames::API_NOVA_ADDRESS_COUNTRY_STATE_INDEX, [RouteParameters::COUNTRY => 'PLACEHOLDER']));
    }

    public function authorizedToDelete(Request $request)
    {
        return false;
    }
}
