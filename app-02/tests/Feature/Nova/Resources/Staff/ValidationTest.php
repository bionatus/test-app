<?php

namespace Tests\Feature\Nova\Resources\Staff;

use App;
use App\Models\Setting;
use App\Models\Staff;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Lang;
use Str;
use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\Nova\TestCase;

class ValidationTest extends TestCase
{
    use RefreshDatabase;

    private string $path;

    protected function setUp(): void
    {
        parent::setUp();

        $this->path = '/nova-api/' . App\Nova\Resources\Staff::uriKey() . DIRECTORY_SEPARATOR;
        Setting::factory()->create(['slug' => Setting::SLUG_STAFF_SMS_NOTIFICATION]);
        Setting::factory()->create(['slug' => Setting::SLUG_STAFF_EMAIL_NOTIFICATION]);
    }

    /** @test */
    public function its_name_must_have_less_than_256_characters_when_creating()
    {
        $name = Str::random(256);

        $response = $this->postJson($this->path, ['name' => $name]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonValidationErrors([
            'name' => Lang::get('validation.max.string', ['attribute' => 'Name', 'max' => 255]),
        ]);
    }

    /** @test */
    public function its_name_must_have_less_than_256_characters_when_updating()
    {
        $name = Str::random(256);

        $staff    = Staff::factory()->counter()->createQuietly();
        $response = $this->putJson($this->path . $staff->getKey(), ['name' => $name]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonValidationErrors([
            'name' => Lang::get('validation.max.string', ['attribute' => 'Name', 'max' => 255]),
        ]);
    }

    /** @test */
    public function its_email_must_have_less_than_256_characters_when_creating()
    {
        $email = Str::random(256);

        $response = $this->postJson($this->path, ['email' => $email]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonValidationErrors([
            'email' => Lang::get('validation.max.string', ['attribute' => 'Email', 'max' => 255]),
        ]);
    }

    /** @test */
    public function its_email_must_have_less_than_256_characters_when_updating()
    {
        $email = Str::random(256);

        $staff    = Staff::factory()->counter()->createQuietly();
        $response = $this->putJson($this->path . $staff->getKey(), ['email' => $email]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonValidationErrors([
            'email' => Lang::get('validation.max.string', ['attribute' => 'Email', 'max' => 255]),
        ]);
    }

    /** @test */
    public function its_email_must_be_a_valid_email_when_creating()
    {
        $response = $this->postJson($this->path, ['email' => 'invalid email']);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonValidationErrors([
            'email' => Lang::get('validation.email', ['attribute' => 'Email']),
        ]);
    }

    /** @test */
    public function its_email_must_be_a_valid_email_when_updating()
    {
        $staff    = Staff::factory()->counter()->createQuietly();
        $response = $this->putJson($this->path . $staff->getKey(), ['email' => 'invalid email']);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonValidationErrors([
            'email' => Lang::get('validation.email', ['attribute' => 'Email']),
        ]);
    }

    /** @test */
    public function its_email_must_end_with_a_valid_top_level_domain_when_creating()
    {
        $response = $this->postJson($this->path, ['email' => 'email@example.invalid']);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonValidationErrors([
            'email' => 'The Email field does not end with a valid tld.',
        ]);
    }

    /** @test */
    public function its_email_must_end_with_a_valid_top_level_domain_when_updating()
    {
        $staff    = Staff::factory()->counter()->createQuietly();
        $response = $this->putJson($this->path . $staff->getKey(), ['email' => 'email@example.invalid']);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonValidationErrors([
            'email' => 'The Email field does not end with a valid tld.',
        ]);
    }

    /** @test */
    public function its_phone_must_have_less_than_256_characters_when_creating()
    {
        $phone = Str::random(256);

        $response = $this->postJson($this->path, ['phone' => $phone]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonValidationErrors([
            'phone' => Lang::get('validation.max.string', ['attribute' => 'Phone Number', 'max' => 255]),
        ]);
    }

    /** @test */
    public function its_phone_must_have_less_than_256_characters_when_updating()
    {
        $phone = Str::random(256);

        $staff    = Staff::factory()->counter()->createQuietly();
        $response = $this->putJson($this->path . $staff->getKey(), ['phone' => $phone]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonValidationErrors([
            'phone' => Lang::get('validation.max.string', ['attribute' => 'Phone Number', 'max' => 255]),
        ]);
    }

    /** @test */
    public function it_should_pass_validation_on_valid_data()
    {
        $supplier = Supplier::factory()->createQuietly();
        $response = $this->postJson($this->path, [
            'name'               => 'A name',
            'email'              => 'staff@email.com',
            'phone'              => '123456',
            'sms_notification'   => true,
            'email_notification' => true,
            'viaResource'        => $supplier->tableName(),
            'viaResourceId'      => $supplier->getKey(),
            'viaRelationship'    => 'counters',
        ]);
        $response->assertJsonMissingValidationErrors();
        $response->assertStatus(Response::HTTP_CREATED);
    }
}
