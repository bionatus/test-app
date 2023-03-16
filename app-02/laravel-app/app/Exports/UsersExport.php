<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UsersExport implements FromCollection, WithMapping, WithHeadings
{
    use Exportable;

    protected $users;

    public function __construct($users)
    {
        $this->users = $users;
    }

    public function collection()
    {
        return $this->users;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Email',
            'Created At',
            'First Name',
            'Last Name',
            'Company',
            'hvac_supplier',
            'occupation',
            'type_of_services',
            'references',
            'address',
            'city',
            'state',
            'zip',
            'country',
            'accreditated',
            'employees',
            'techs_number',
            'service_manager_name',
            'service_manager_phone',
            'accreditated_at',
            'Apps',
            'group_code',
            'calls_count',
            'manuals_count',
            'call_date',
            'call_count',
            'hubspot_id',
            'registration_completed',
            'registration_completed_at',
            'access_code',
            'Photo',
            'bio',
            'job_title',
            'union',
            'experience_years',
            'term',
        ];
    }

    public function map($user): array
    {
        return [
            $user->id,
            $user->email,
            $user->created_at,
            $user->first_name,
            $user->last_name,
            $user->company,
            $user->hvac_supplier,
            $user->occupation,
            $user->type_of_services,
            $user->references,
            $user->address,
            $user->city,
            $user->state,
            $user->zip,
            $user->country,
            $user->accreditated,
            $user->employees,
            $user->techs_number,
            $user->service_manager_name,
            $user->service_manager_phone,
            $user->accreditated_at,
            $user->apps,
            $user->group_code,
            $user->calls_count,
            $user->manuals_count,
            $user->call_date,
            $user->call_count,
            $user->hubspot_id,
            $user->registration_completed,
            $user->registration_completed_at,
            $user->access_code,
            $user->photo,
            $user->job_title,
            $user->union,
            $user->experience_years,
            $user->terms,
        ];
    }
}
