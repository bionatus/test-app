<?php

namespace App\Nova;

use Fourstacks\NovaCheckboxes\Checkboxes;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Country;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Image;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Password;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;

class User extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\\User';
    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';
    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'name',
        'email',
    ];

    public static $group = 'Legacy';

    /**
     * Get the fields displayed by the resource.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make()->sortable(),

            Text::make('First Name')->sortable()->rules('required', 'max:255'),

            Text::make('Last Name')->sortable()->rules('required', 'max:255'),

            Text::make('Email')
                ->sortable()
                ->rules('required', 'email', 'max:254')
                ->creationRules('unique:users,email')
                ->updateRules('unique:users,email,{{resourceId}}'),

            Password::make('Password')
                ->onlyOnForms()
                ->creationRules('required', 'string', 'min:6')
                ->updateRules('nullable', 'string', 'min:6'),

            Text::make('Company'),

            Text::make('HVAC supplier', 'hvac_supplier')->hideFromIndex(),

            Select::make('Occupation')->options([
                    'technician'      => 'Technician',
                    'service-manager' => 'Service Manager',
                    'sales-person'    => 'Sales Person',
                    'owner'           => 'Business Owner',
                    'supplier'        => 'HVAC Supplier',
                    'other'           => 'Other',
                ])->displayUsingLabels()->hideFromIndex(),

            Select::make('We Mostly do', 'type_of_services')->options([
                    'commercial'                 => 'Commercial',
                    'residential'                => 'Residential',
                    'commercial-and-residential' => 'Commercial &amp; Residential',
                    'other'                      => 'Other/NA',
                ])->displayUsingLabels()->hideFromIndex(),

            Checkboxes::make('Apps')->options([
                    'ServiceTitan'   => 'ServiceTitan®',
                    'measureQuick'   => 'measureQuick®',
                    'XOi'            => 'XOi®',
                    'Service Fusion' => 'Service Fusion®',
                    'FieldPulse'     => 'FieldPulse®',
                ]),

            Text::make('Techs Number', 'techs_number')->hideFromIndex(),

            Text::make('Service Manager Name', 'service_manager_name')->hideFromIndex(),

            Text::make('Service Manager Phone', 'service_manager_phone')->hideFromIndex(),

            Text::make('Phone')->hideFromIndex(),

            Text::make('Address')->hideFromIndex(),

            Text::make('City')->hideFromIndex(),

            Text::make('State')->hideFromIndex(),

            Text::make('Postal Code', 'zip')->hideFromIndex(),

            Country::make('Country', 'country')->hideFromIndex(),

            Date::make('Created At')->hideWhenCreating()->hideWhenUpdating(),

            Boolean::make('Registration Completed')->hideFromIndex(),

            Date::make('Registration Completed At')->hideFromIndex(),

            Text::make('Access Code')->hideFromIndex(),

            Boolean::make('Accreditated')->hideFromIndex(),

            Date::make('Accreditated At')->hideFromIndex(),

            Text::make('Group Code', 'group_code')->hideFromIndex(),

            Number::make('Calls Counter', 'calls_count')->hideFromIndex(),

            Number::make('Manuals Counter', 'manuals_count')->hideFromIndex(),

            Textarea::make('Bio')->hideFromIndex(),

            Image::make('Photo'),

            Text::make('Job Title')->hideFromIndex(),

            Text::make('Union')->hideFromIndex(),

            Number::make('Experience Years')->hideFromIndex(),

            Date::make('Updated At')->hideFromIndex(),

            Select::make('Role')->options([
                    'contractor'    => 'User',
                    'administrator' => 'Administrator',
                ]),

            Boolean::make('Terms & Conditions', 'terms')->hideFromIndex(),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function filters(Request $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function actions(Request $request)
    {
        $user = auth()->user();

        return [
            new Actions\ExportUsers($user),
        ];
    }
}
