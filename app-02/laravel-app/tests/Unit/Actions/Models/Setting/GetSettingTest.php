<?php

namespace Tests\Unit\Actions\Models\Setting;

use App\Actions\Models\Setting\GetSetting;
use App\Models\HasSetting;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use ReflectionClass;
use ReflectionMethod;
use Tests\TestCase;

class GetSettingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_gets_setting_default_value()
    {
        $slugSms = Setting::SLUG_ORDER_IS_READY_FOR_APPROVAL_SMS;
        Setting::factory()->groupNotification()->applicableToUser()->create([
            'slug'  => $slugSms,
            'value' => true,
        ]);
        /** @var User $user */
        $user = User::factory()->createQuietly();

        $getSetting = $this->getSettingStub($user, $slugSms);

        $this->assertTrue($getSetting->execute());
    }

    /** @test */
    public function it_has_expected_methods()
    {
        $expectedMethods = [
            '__construct',
            'execute',
            'completeSettingQuery',
            'getRelationship',
        ];

        $reflection = new ReflectionClass(GetSetting::class);

        Collection::make($reflection->getMethods())->each(function (ReflectionMethod $method) use ($expectedMethods) {
            $this->assertTrue(in_array($method->name, $expectedMethods));
        });
    }

    private function getSettingStub(HasSetting $model, string $slug)
    {
        return new class($model, $slug) extends GetSetting {
        };
    }
}
