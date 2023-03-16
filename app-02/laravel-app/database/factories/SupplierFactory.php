<?php

namespace Database\Factories;

use App\Models\Supplier;
use App\Models\SupplierCompany;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @method Collection|Supplier create($attributes = [], ?Model $parent = null)
 * @method Collection|Supplier createQuietly($attributes = [], ?Model $parent = null)
 * @method Collection|Supplier make($attributes = [], ?Model $parent = null)
 */
class SupplierFactory extends Factory
{
    public function definition(): array
    {
        return [
            'uuid'            => $this->faker->unique()->uuid,
            'name'            => $this->faker->company,
            'take_rate'       => $this->faker->numberBetween(1, 10000),
            'take_rate_until' => $this->faker->date(),
        ];
    }

    public function full(): self
    {
        return $this->state(function() {
            return [
                'airtable_id'             => $this->faker->unique()->numberBetween(1, 1000),
                'branch'                  => $this->faker->numberBetween(1, 99999999),
                'address'                 => $this->faker->streetAddress,
                'address_2'               => $this->faker->streetName,
                'city'                    => $this->faker->city,
                'state'                   => $this->faker->state,
                'zip_code'                => $this->faker->postcode,
                'country'                 => 'US',
                'latitude'                => $this->faker->latitude,
                'longitude'               => $this->faker->longitude,
                'phone'                   => $this->faker->phoneNumber,
                'prokeep_phone'           => $this->faker->phoneNumber,
                'offers_delivery'         => $this->faker->boolean,
                'about'                   => $this->faker->text(),
                'fax'                     => $this->faker->phoneNumber,
                'email'                   => $this->faker->unique()->email,
                'contact_name'            => $this->faker->name,
                'contact_phone'           => $this->faker->phoneNumber,
                'contact_email'           => $this->faker->companyEmail,
                'contact_secondary_email' => $this->faker->companyEmail,
                'contact_job'             => $this->faker->jobTitle,
                'url'                     => $this->faker->url,
                'terms'                   => Supplier::DEFAULT_PAYMENT_TERMS,
                'monday_hours'            => '7:00AM-5:00PM',
                'tuesday_hours'           => '7:00AM-5:00PM',
                'wednesday_hours'         => '7:00AM-5:00PM',
                'thursday_hours'          => '7:00AM-5:00PM',
                'friday_hours'            => '7:00AM-5:00PM',
                'saturday_hours'          => '7:00AM-5:00PM',
                'sunday_hours'            => '7:00AM-5:00PM',
            ];
        });
    }

    public function usingSupplierCompany(SupplierCompany $supplierCompany): self
    {
        return $this->state(function() use ($supplierCompany) {
            return [
                'supplier_company_id' => $supplierCompany,
            ];
        });
    }

    public function published(): self
    {
        return $this->state(function() {
            return [
                'published_at' => Carbon::now(),
            ];
        });
    }

    public function onTheNetwork(): self
    {
        return $this->state(function() {
            return [
                'published_at' => Carbon::now(),
                'verified_at'  => Carbon::now(),
            ];
        });
    }

    public function unpublished(): self
    {
        return $this->state(function() {
            return [
                'published_at' => null,
            ];
        });
    }

    public function verified(): self
    {
        return $this->state(function() {
            return [
                'verified_at' => Carbon::now(),
            ];
        });
    }

    public function unverified(): self
    {
        return $this->state(function() {
            return [
                'verified_at' => null,
            ];
        });
    }

    public function withEmail(): self
    {
        return $this->state(function() {
            return [
                'email' => $this->faker->email,
            ];
        });
    }
}
