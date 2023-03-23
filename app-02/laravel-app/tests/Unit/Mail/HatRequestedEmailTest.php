<?php

namespace Tests\Unit\Mail;

use App\Mail\HatRequestedEmail;
use App\Models\User;
use Mockery;
use ReflectionException;
use Str;
use Tests\TestCase;

class HatRequestedEmailTest extends TestCase
{
    /** @test
     * @throws ReflectionException
     */
    public function it_builds_a_hat_requested_email()
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getKey')->withNoArgs()->once()->andReturn($id = 123456789);
        $user->shouldReceive('getAttribute')->withArgs(['first_name'])->once()->andReturn($firstName = 'Jon');
        $user->shouldReceive('getAttribute')->withArgs(['last_name'])->once()->andReturn($lastName = 'Doe');
        $user->shouldReceive('getAttribute')->withArgs(['email'])->once()->andReturn($email = 'jon@doe.com');
        $user->shouldReceive('getAttribute')
            ->withArgs(['address'])
            ->once()
            ->andReturn($address = '742 Evergreen Terrace');
        $user->shouldReceive('getAttribute')->withArgs(['address_2'])->once()->andReturn($address2 = '6th floor');
        $user->shouldReceive('getAttribute')->withArgs(['city'])->once()->andReturn($city = 'Springfield');
        $user->shouldReceive('getAttribute')->withArgs(['state'])->once()->andReturn($state = 'US-AR');
        $user->shouldReceive('getAttribute')->withArgs(['country'])->once()->andReturn($country = 'US');
        $user->shouldReceive('getAttribute')->withArgs(['zip'])->once()->andReturn($zipCode = '55522');

        $mailable = new HatRequestedEmail($user);

        $render = $mailable->render();
        $this->assertTrue(Str::contains($render, $firstName));
        $this->assertTrue(Str::contains($render, $lastName));
        $this->assertTrue(Str::contains($render, $id));
        $this->assertTrue(Str::contains($render, $email));
        $this->assertTrue(Str::contains($render, $address));
        $this->assertTrue(Str::contains($render, $address2));
        $this->assertTrue(Str::contains($render, $city));
        $this->assertTrue(Str::contains($render, $state));
        $this->assertTrue(Str::contains($render, $country));
        $this->assertTrue(Str::contains($render, $zipCode));
    }
}
