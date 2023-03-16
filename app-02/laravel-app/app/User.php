<?php

namespace App;

use Illuminate\Support\Collection;
use Laravel\Spark\User as SparkUser;
use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Str;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends SparkUser implements JWTSubject
{
    use HasApiTokens, Notifiable, HasRoles;
    use HasSlug {
        generateNonUniqueSlug as traitGenerateNonUniqueSlug;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'public_name',
        'legacy_password',
        'legacy_id',
        'role',
        'phone',
        'company',
        'hvac_supplier',
        'occupation',
        'type_of_services',
        'references',
        'address',
        'city',
        'state',
        'zip',
        'country',
        'name',
        'email',
        'password',
        'accreditated',
        'accreditated_at',
        'apps',
        'group_code',
        'calls_count',
        'manuals_count',
        'call_date',
        'call_count',
        'hubspot_id',
        'registration_completed',
        'registration_completed_at',
        'access_code',
        'photo',
        'bio',
        'job_title',
        'union',
        'experience_years',
        'terms',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'authy_id',
        'country_code',
        'phone',
        'two_factor_reset_code',
        'card_brand',
        'card_last_four',
        'card_country',
        'billing_address',
        'billing_address_line_2',
        'billing_city',
        'billing_zip',
        'billing_country',
        'extra_billing_information',
        // 'user_login',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'trial_ends_at' => 'datetime',
        'uses_two_factor_auth' => 'boolean',
        'accreditated' => 'boolean',
        'accreditated_at' => 'date',
        'apps' => 'array',
        'call_date' => 'datetime',
        'registration_completed' => 'boolean',
        'registration_completed_at' => 'datetime',
        'terms' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Get the Application Notifications related to the User
     */
    public function app_notifications()
    {
        return $this->hasMany('App\AppNotification');
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(['first_name', 'last_name'])
            ->saveSlugsTo('public_name')
            ->doNotGenerateSlugsOnUpdate()
            ->usingSeparator('');
    }

    /** @noinspection DuplicatedCode */
    protected function generateNonUniqueSlug(): string
    {
        $slugField = $this->slugOptions->slugField;
        if ($this->hasCustomSlugBeenUsed() && !empty($this->getAttribute($slugField))) {
            return $this->getAttribute($slugField);
        }

        $separator = $this->slugOptions->slugSeparator;

        $this->slugOptions->slugSeparator = '-';

        $generatedSlug = $this->traitGenerateNonUniqueSlug();

        $slugSourceString = $this->getSlugSourceString();
        if ($this->slugOptions->slugSeparator === $slugSourceString || is_numeric(substr($slugSourceString, 0, 1))) {
            return 'User';
        }

        $explodedSlug = Collection::make(explode($this->slugOptions->slugSeparator, $generatedSlug));

        $this->slugOptions->slugSeparator = $separator;

        return $explodedSlug->map(fn(string $segment) => Str::ucfirst($segment))->join($separator);
    }
}
