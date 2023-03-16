<?php

namespace App\Models;

use Illuminate\Auth;
use Illuminate\Contracts;
use Illuminate\Foundation\Auth\Access\Authorizable;

class Authenticatable extends Model implements Contracts\Auth\Access\Authorizable, Contracts\Auth\Authenticatable
{
    use Auth\Authenticatable;
    use Authorizable;
}
