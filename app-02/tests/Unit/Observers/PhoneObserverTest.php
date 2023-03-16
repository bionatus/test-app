<?php

namespace Tests\Unit\Observers;

use App\Events\User\HubspotFieldUpdated;
use App\Jobs\LogActivity;
use App\Models\Phone;
use App\Models\User;
use App\Observers\PhoneObserver;
use Bus;
use Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Mockery;
use Tests\TestCase;

class PhoneObserverTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_should_dispatch_user_hubspot_field_updated_when_created_with_an_user_associated()
    {
        Event::fake(HubspotFieldUpdated::class);

        $user  = Mockery::mock(User::class);
        $phone = Mockery::mock(Phone::class);
        $phone->shouldReceive('getAttribute')->with('user')->twice()->andReturn($user);

        $observer = new PhoneObserver();
        $observer->created($phone);

        Event::assertDispatched(function(HubspotFieldUpdated $event) use ($user) {
            $this->assertSame($user, $event->user());

            return true;
        });
    }

    /** @test */
    public function it_should_not_dispatch_user_hubspot_field_updated_when_created_without_an_user_associated()
    {
        Event::fake(HubspotFieldUpdated::class);

        $phone = Mockery::mock(Phone::class);
        $phone->shouldReceive('getAttribute')->with('user')->once()->andReturnNull();

        $observer = new PhoneObserver();
        $observer->created($phone);

        Event::assertNotDispatched(HubspotFieldUpdated::class);
    }

    /** @test
     *
     * @param  array  $dirtyAttributes
     * @param  bool  $shouldDispatch
     *
     * @dataProvider dirtyFieldsDataProvider
     */
    public function it_should_dispatch_user_hubspot_field_updated_when_updated_the_number_or_country_code_having_a_user(
        array $dirtyAttributes,
        bool $shouldDispatch
    ) {
        Event::fake(HubspotFieldUpdated::class);

        $user  = User::factory()->create();
        $phone = Phone::factory()->make();
        $phone->user()->associate($user);
        $phone->saveQuietly();

        Collection::make($dirtyAttributes)->each(function($value, $attribute) use ($phone) {
            $phone->setAttribute($attribute, $value);
        });
        $phone->save();

        Event::assertDispatchedTimes(HubspotFieldUpdated::class, (int) $shouldDispatch);
        if ($shouldDispatch) {
            Event::assertDispatched(function(HubspotFieldUpdated $event) use ($user) {
                $this->assertSame($user->getKey(), $event->user()->getKey());

                return true;
            });
        }
    }

    /** @test
     *
     * @param  array  $dirtyAttributes
     *
     * @dataProvider dirtyFieldsDataProvider
     */
    public function it_should_not_dispatch_user_hubspot_field_updated_without_having_a_user(
        array $dirtyAttributes
    ) {
        Event::fake(HubspotFieldUpdated::class);

        $phone = Phone::factory()->make();
        $phone->saveQuietly();

        Collection::make($dirtyAttributes)->each(function($value, $attribute) use ($phone) {
            $phone->setAttribute($attribute, $value);
        });
        $phone->save();

        Event::assertNotDispatched(HubspotFieldUpdated::class);
    }

    /** @test
     *
     * @param  array  $dirtyAttributes
     *
     * @dataProvider dirtyFieldsDataProvider
     */
    public function it_should_dispatch_user_hubspot_field_updated_when_updated_the_number_or_country_code_and_associating_a_user(
        array $dirtyAttributes
    ) {
        Event::fake(HubspotFieldUpdated::class);

        $user  = User::factory()->create();
        $phone = Phone::factory()->make();
        $phone->saveQuietly();

        Collection::make($dirtyAttributes)->each(function($value, $attribute) use ($phone) {
            $phone->setAttribute($attribute, $value);
        });
        $phone->user()->associate($user);
        $phone->save();

        Event::assertDispatched(function(HubspotFieldUpdated $event) use ($user) {
            $this->assertSame($user->getKey(), $event->user()->getKey());

            return true;
        });
    }

    /** @test
     *
     * @param  array  $dirtyAttributes
     *
     * @dataProvider dirtyFieldsDataProvider
     */
    public function it_should_dispatch_user_hubspot_field_updated_the_user_is_disassociating_a_user(
        array $dirtyAttributes
    ) {
        Event::fake(HubspotFieldUpdated::class);

        $user  = User::factory()->create();
        $phone = Phone::factory()->make();
        $phone->user()->associate($user);
        $phone->saveQuietly();

        Collection::make($dirtyAttributes)->each(function($value, $attribute) use ($phone) {
            $phone->setAttribute($attribute, $value);
        });
        $phone->user()->disassociate();
        $phone->save();

        Event::assertDispatched(function(HubspotFieldUpdated $event) use ($user) {
            $this->assertSame($user->getKey(), $event->user()->getKey());

            return true;
        });
    }

    public function dirtyFieldsDataProvider(): array
    {
        return [
            [['updated_at' => Carbon::now()], false],
            [['country_code' => 1], true],
            [['number' => 1], true],
            [['country_code' => 1, 'number' => 1], true],
        ];
    }

    /** @test
     *
     * @param  array  $dirtyAttributes
     *
     * @dataProvider dirtyFieldsToLogActivityDataProvider
     */
    public function it_dispatch_a_log_when_number_is_updated(
        array $dirtyAttributes,
        bool $shouldDispatch
    ) {
        Bus::fake();

        $phone = Phone::factory()->make();
        $phone->saveQuietly();

        Collection::make($dirtyAttributes)->each(function($value, $attribute) use ($phone) {
            $phone->setAttribute($attribute, $value);
        });

        $phone->save();
        $times = 0;
        if ($shouldDispatch) {
            $times++;
        }
        Bus::assertDispatchedTimes(LogActivity::class, $times);
    }

    public function dirtyFieldsToLogActivityDataProvider(): array
    {
        return [
            [['updated_at' => Carbon::now()], false],
            [['country_code' => 1], false],
            [['number' => 1], true],
            [['country_code' => 1, 'number' => 1], true],
        ];
    }

    /** @test */
    public function it_should_dispatch_user_hubspot_field_updated_when_deleted_if_the_phone_has_a_user()
    {
        Event::fake(HubspotFieldUpdated::class);
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getKey')->withNoArgs()->twice()->andReturn(1);

        $model = Mockery::mock(Phone::class);
        $model->shouldReceive('getAttribute')->withArgs(['user'])->twice()->andReturn($user);

        $observer = new PhoneObserver();

        $observer->deleted($model);

        Event::assertDispatched(function(HubspotFieldUpdated $event) use ($user) {
            $this->assertSame($user->getKey(), $event->user()->getKey());

            return true;
        });
    }

    /** @test */
    public function it_should_not_dispatch_user_hubspot_field_updated_when_deleted_if_the_phone_does_not_have_a_user()
    {
        Event::fake(HubspotFieldUpdated::class);

        $model = Mockery::mock(Phone::class);
        $model->shouldReceive('getAttribute')->withArgs(['user'])->once()->andReturnNull();

        $observer = new PhoneObserver();

        $observer->deleted($model);

        Event::assertNotDispatched(HubspotFieldUpdated::class);
    }
}
