<?php

namespace App\Rules\CompanyUser;

use App\Models\Company;
use App\Models\Scopes\ByUuid;
use App\Models\User;
use Illuminate\Contracts\Validation\Rule;

class Exists implements Rule
{
    protected User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function passes($attribute, $value)
    {
        return !!Company::scoped(new ByUuid($value))->whereHas('companyUsers', function($query) {
            return $query->where('user_id', '=', $this->user->getKey());
        })->count();
    }

    public function message()
    {
        return 'The company must be related to the user.';
    }
}
