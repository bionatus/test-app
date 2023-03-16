<?php

namespace Tests\Unit\Nova\Resources;

use App\Constants\MediaCollectionNames;
use App\Models\User;
use App\Nova\Resources;

class UserTest extends ResourceTestCase
{
    /** @test */
    public function it_uses_correct_model()
    {
        $this->assertSame(User::class, Resources\User::$model);
    }

    /** @test */
    public function it_uses_the_name_as_title()
    {
        $this->assertSame('name', Resources\User::$title);
    }

    /** @test */
    public function it_uses_fields_for_search()
    {
        $this->assertSame([
            'id',
            'name',
            'email',
        ], Resources\User::$search);
    }

    /** @test */
    public function it_should_be_displayed_in_navigation()
    {
        $this->assertTrue(Resources\User::$displayInNavigation);
    }

    /** @test */
    public function it_uses_a_custom_uri()
    {
        $this->assertEquals('latam-users', Resources\User::uriKey());
    }

    /** @test */
    public function it_uses_current_as_group()
    {
        $this->assertEquals('Current', Resources\User::group());
    }

    /** @test */
    public function it_as_expected_fields()
    {
        $this->assertHasExpectedFields(Resources\User::class, [
            'id',
            'first_name',
            'last_name',
            'email',
            'password',
            'verified',
            'disabled_at',
            'created_at',
            MediaCollectionNames::IMAGES,
            'address',
            'address_2',
            'country',
            'state',
            'zip',
            'city',
            'company_name',
            'company_type',
            'primary_equipment_type',
            'display_primary_equipment_type',
            'job_title',
            'display_job_title',
            'company_country',
            'company_state',
            'company_city',
            'company_address',
            'company_zip_code',
            'phone',
            'country_code',
            'points',
            'suppliers',
            'ComputedField',
            'hubspot_form',
        ]);
    }
}
