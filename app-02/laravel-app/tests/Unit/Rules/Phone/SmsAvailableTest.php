<?php

namespace Tests\Unit\Rules\Phone;

use App;
use App\Constants\RequestKeys;
use App\Models\Phone;
use App\Rules\Phone\SmsAvailable;
use Config;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SmsAvailableTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_a_message()
    {
        Carbon::setTestNow('2021-01-01');

        $now  = Carbon::now();
        $rule = new SmsAvailable();

        $this->assertSame('The :attribute will be available at ' . $now, $rule->message());
    }

    /** @test */
    public function it_passes_if_there_is_no_country_code()
    {
        $request = Request::create('', 'POST', []);
        App::bind(Request::class, fn() => $request);

        $rule = new SmsAvailable();

        $this->assertTrue($rule->passes('attribute', 'invalid'));
    }

    /** @test */
    public function it_passes_if_the_phone_does_not_exist()
    {
        $request = Request::create('', 'POST', [
            RequestKeys::COUNTRY_CODE => '1',
        ]);
        $rule    = new SmsAvailable();

        App::bind('request', fn() => $request);

        $this->assertTrue($rule->passes('attribute', 'invalid'));
    }

    /** @test */
    public function it_passes()
    {
        $phone   = Phone::factory()->create();
        $request = Request::create('', 'POST', [
            RequestKeys::COUNTRY_CODE => $phone->country_code,
        ]);

        App::bind('request', fn() => $request);

        $rule = new SmsAvailable();

        $this->assertTrue($rule->passes('attribute', $phone->number));
    }

    /** @test */
    public function it_returns_no_phone_if_there_is_no_validation()
    {
        $rule = new SmsAvailable();

        $this->assertNull($rule->phone());
    }

    /** @test */
    public function it_returns_no_phone_if_validation_fails()
    {
        Config::set('communications.sms.code.retry_after', [30]);
        $phone   = Phone::factory()->create();
        $request = Request::create('', 'POST', [
            RequestKeys::COUNTRY_CODE => $phone->country_code,
        ]);

        App::bind('request', fn() => $request);

        $rule = new SmsAvailable();

        $this->assertNull($rule->phone());
    }

    /** @test */
    public function it_returns_phone_if_validation_passes()
    {
        Config::set('communications.sms.code.retry_after', [30]);
        $phone   = Phone::factory()->create();
        $request = Request::create('', 'POST', [
            RequestKeys::COUNTRY_CODE => $phone->country_code,
        ]);

        App::bind('request', fn() => $request);

        $rule = new SmsAvailable();

        $phone   = Phone::factory()->create();
        $request = Request::create('', 'POST', [
            RequestKeys::COUNTRY_CODE => $phone->country_code,
        ]);

        App::bind('request', fn() => $request);
        $rule->passes('attribute', $phone->number);

        $this->assertEquals($phone->getKey(), $rule->phone()->getKey());
    }
}
