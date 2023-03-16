<?php

namespace Tests\Unit\Http\Resources\Api\V3\Account\Point;

use App\Http\Resources\Api\V3\Account\Point\BaseResource;
use App\Models\AppSetting;
use App\Models\User;
use Mockery;
use Tests\TestCase;

class BaseResourceTest extends TestCase
{
    /** @test
     * @dataProvider dataProvider
     */
    public function it_has_correct_fields(
        bool $isSupportCallDisabled,
        int $points,
        bool $supportCallEnabled
    ) {
        $appSettingMultiplier = Mockery::mock(AppSetting::class);
        $appSettingMultiplier->shouldReceive('getAttribute')->withArgs(['value'])->once()->andReturn($multiplier = 2);

        $user = Mockery::mock(User::class);
        $user->shouldReceive('availablePoints')->withNoArgs()->once()->andReturn($availablePoints = 70);
        $user->shouldReceive('totalPointsEarned')->withNoArgs()->once()->andReturn($earnedPoints = 100);
        $user->shouldReceive('availablePointsToCash')->withNoArgs()->once()->andReturn($availablePointsToCash = 7.00);
        $user->shouldReceive('isSupportCallDisabled')->withNoArgs()->once()->andReturn($isSupportCallDisabled);
        $user->shouldReceive('totalPointsEarned')->andReturn($points);

        $resource = new BaseResource($user, $appSettingMultiplier);

        $response = $resource->resolve();

        $data = [
            'available_points'     => $availablePoints,
            'earned_points'        => $earnedPoints,
            'cash_value'           => $availablePointsToCash,
            'multiplier'           => $multiplier,
            'support_call_enabled' => $supportCallEnabled,
        ];

        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
        $this->assertEquals($data, $response);
    }

    public function dataProvider(): array
    {
        return [
            // isSupportCallDisabled, points, supportCallEnabled
            [true, 999, false],
            [true, 1000, true],
            [false, 999, true],
            [false, 1000, true],
        ];
    }
}
