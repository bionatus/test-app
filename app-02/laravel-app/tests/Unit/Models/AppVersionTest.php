<?php

namespace Tests\Unit\Models;

use App\Models\AppVersion;
use App\Models\Flag;
use App\Models\User;
use Illuminate\Support\Carbon;
use Lang;

class AppVersionTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(AppVersion::tableName(), [
            'id',
            'min',
            'current',
            'video_title',
            'video_url',
            'message',
            'created_at',
            'updated_at',
        ]);
    }

    /**
     * @test
     * @dataProvider needsConfirmProvider
     */
    public function it_knows_if_client_app_needs_confirm(
        bool $hasFlag,
        string $currentVersion,
        string $clientVersion,
        bool $hasVideoUrl,
        bool $isUserVerified,
        bool $verifiedADayOlder,
        bool $isUserRegistered,
        bool $registeredADayOlder,
        bool $expected
    ) {
        $videoUrl   = $hasVideoUrl ? "https://video-url.com" : null;
        $appVersion = AppVersion::factory()->create(['current' => $currentVersion, 'video_url' => $videoUrl]);

        $verifiedAtDate = null;
        if ($isUserVerified) {
            $verifiedAtDate = $verifiedADayOlder ? Carbon::yesterday() : Carbon::now();
        }

        $registerCompletedAtDate = null;
        if ($isUserRegistered) {
            $registerCompletedAtDate = $registeredADayOlder ? Carbon::yesterday() : Carbon::now();
        }

        $user = User::factory()->create([
            'verified_at'               => $verifiedAtDate,
            'registration_completed_at' => $registerCompletedAtDate,
        ]);

        if ($hasFlag) {
            $flag = Lang::get(Flag::APP_VERSION_CONFIRM, ['app_version' => $currentVersion]);
            Flag::factory()->usingModel($user)->create(['name' => $flag]);
        }

        $this->assertSame($expected, $appVersion->needsConfirm($clientVersion, $user));
    }

    public function needsConfirmProvider(): array
    {
        // hasFlag, currentVersion, clientVersion, hasVideoUrl, isUserVerified, verifiedADayOlder, isUserRegistered, registeredADayOlder, expected
        return [
            [false, '2.2.2', '1.3.2', false, false, false, false, false, false],
            [false, '2.2.2', '1.3.2', false, false, false, true, false, false],
            [false, '2.2.2', '1.3.2', false, false, false, true, true, false],
            [false, '2.2.2', '1.3.2', false, true, false, false, false, false],
            [false, '2.2.2', '1.3.2', false, true, false, true, false, false],
            [false, '2.2.2', '1.3.2', false, true, false, true, true, false],
            [false, '2.2.2', '1.3.2', false, true, true, false, false, false],
            [false, '2.2.2', '1.3.2', false, true, true, true, false, false],
            [false, '2.2.2', '1.3.2', false, true, true, true, true, false],
            [false, '2.2.2', '1.3.2', true, false, false, false, false, false],
            [false, '2.2.2', '1.3.2', true, false, false, true, false, false],
            [false, '2.2.2', '1.3.2', true, false, false, true, true, false],
            [false, '2.2.2', '1.3.2', true, true, false, false, false, false],
            [false, '2.2.2', '1.3.2', true, true, false, true, false, false],
            [false, '2.2.2', '1.3.2', true, true, false, true, true, false],
            [false, '2.2.2', '1.3.2', true, true, true, false, false, false],
            [false, '2.2.2', '1.3.2', true, true, true, true, false, false],
            [false, '2.2.2', '1.3.2', true, true, true, true, true, false],
            [false, '2.2.2', '2.1.3', false, false, false, false, false, false],
            [false, '2.2.2', '2.1.3', false, false, false, true, false, false],
            [false, '2.2.2', '2.1.3', false, false, false, true, true, false],
            [false, '2.2.2', '2.1.3', false, true, false, false, false, false],
            [false, '2.2.2', '2.1.3', false, true, false, true, false, false],
            [false, '2.2.2', '2.1.3', false, true, false, true, true, false],
            [false, '2.2.2', '2.1.3', false, true, true, false, false, false],
            [false, '2.2.2', '2.1.3', false, true, true, true, false, false],
            [false, '2.2.2', '2.1.3', false, true, true, true, true, false],
            [false, '2.2.2', '2.1.3', true, false, false, false, false, false],
            [false, '2.2.2', '2.1.3', true, false, false, true, false, false],
            [false, '2.2.2', '2.1.3', true, false, false, true, true, false],
            [false, '2.2.2', '2.1.3', true, true, false, false, false, false],
            [false, '2.2.2', '2.1.3', true, true, false, true, false, false],
            [false, '2.2.2', '2.1.3', true, true, false, true, true, false],
            [false, '2.2.2', '2.1.3', true, true, true, false, false, false],
            [false, '2.2.2', '2.1.3', true, true, true, true, false, false],
            [false, '2.2.2', '2.1.3', true, true, true, true, true, false],
            [false, '2.2.2', '2.2.1', false, false, false, false, false, false],
            [false, '2.2.2', '2.2.1', false, false, false, true, false, false],
            [false, '2.2.2', '2.2.1', false, false, false, true, true, false],
            [false, '2.2.2', '2.2.1', false, true, false, false, false, false],
            [false, '2.2.2', '2.2.1', false, true, false, true, false, false],
            [false, '2.2.2', '2.2.1', false, true, false, true, true, false],
            [false, '2.2.2', '2.2.1', false, true, true, false, false, false],
            [false, '2.2.2', '2.2.1', false, true, true, true, false, false],
            [false, '2.2.2', '2.2.1', false, true, true, true, true, false],
            [false, '2.2.2', '2.2.1', true, false, false, false, false, false],
            [false, '2.2.2', '2.2.1', true, false, false, true, false, false],
            [false, '2.2.2', '2.2.1', true, false, false, true, true, false],
            [false, '2.2.2', '2.2.1', true, true, false, false, false, false],
            [false, '2.2.2', '2.2.1', true, true, false, true, false, false],
            [false, '2.2.2', '2.2.1', true, true, false, true, true, false],
            [false, '2.2.2', '2.2.1', true, true, true, false, false, false],
            [false, '2.2.2', '2.2.1', true, true, true, true, false, false],
            [false, '2.2.2', '2.2.1', true, true, true, true, true, false],
            [false, '2.2.2', '2.2.2', false, false, false, false, false, false],
            [false, '2.2.2', '2.2.2', false, false, false, true, false, false],
            [false, '2.2.2', '2.2.2', false, false, false, true, true, false],
            [false, '2.2.2', '2.2.2', false, true, false, false, false, false],
            [false, '2.2.2', '2.2.2', false, true, false, true, false, false],
            [false, '2.2.2', '2.2.2', false, true, false, true, true, false],
            [false, '2.2.2', '2.2.2', false, true, true, false, false, false],
            [false, '2.2.2', '2.2.2', false, true, true, true, false, false],
            [false, '2.2.2', '2.2.2', false, true, true, true, true, false],
            [false, '2.2.2', '2.2.2', true, false, false, false, false, false],
            [false, '2.2.2', '2.2.2', true, false, false, true, false, false],
            [false, '2.2.2', '2.2.2', true, false, false, true, true, true],
            [false, '2.2.2', '2.2.2', true, true, false, false, false, false],
            [false, '2.2.2', '2.2.2', true, true, false, true, false, false],
            [false, '2.2.2', '2.2.2', true, true, false, true, true, false],
            [false, '2.2.2', '2.2.2', true, true, true, false, false, true],
            [false, '2.2.2', '2.2.2', true, true, true, true, false, false],
            [false, '2.2.2', '2.2.2', true, true, true, true, true, true],
            [false, '2.2.2', '2.2.3', false, false, false, false, false, false],
            [false, '2.2.2', '2.2.3', false, false, false, true, false, false],
            [false, '2.2.2', '2.2.3', false, false, false, true, true, false],
            [false, '2.2.2', '2.2.3', false, true, false, false, false, false],
            [false, '2.2.2', '2.2.3', false, true, false, true, false, false],
            [false, '2.2.2', '2.2.3', false, true, false, true, true, false],
            [false, '2.2.2', '2.2.3', false, true, true, false, false, false],
            [false, '2.2.2', '2.2.3', false, true, true, true, false, false],
            [false, '2.2.2', '2.2.3', false, true, true, true, true, false],
            [false, '2.2.2', '2.2.3', true, false, false, false, false, false],
            [false, '2.2.2', '2.2.3', true, false, false, true, false, false],
            [false, '2.2.2', '2.2.3', true, false, false, true, true, false],
            [false, '2.2.2', '2.2.3', true, true, false, false, false, false],
            [false, '2.2.2', '2.2.3', true, true, false, true, false, false],
            [false, '2.2.2', '2.2.3', true, true, false, true, true, false],
            [false, '2.2.2', '2.2.3', true, true, true, false, false, false],
            [false, '2.2.2', '2.2.3', true, true, true, true, false, false],
            [false, '2.2.2', '2.2.3', true, true, true, true, true, false],
            [false, '2.2.2', '2.3.1', false, false, false, false, false, false],
            [false, '2.2.2', '2.3.1', false, false, false, true, false, false],
            [false, '2.2.2', '2.3.1', false, false, false, true, true, false],
            [false, '2.2.2', '2.3.1', false, true, false, false, false, false],
            [false, '2.2.2', '2.3.1', false, true, false, true, false, false],
            [false, '2.2.2', '2.3.1', false, true, false, true, true, false],
            [false, '2.2.2', '2.3.1', false, true, true, false, false, false],
            [false, '2.2.2', '2.3.1', false, true, true, true, false, false],
            [false, '2.2.2', '2.3.1', false, true, true, true, true, false],
            [false, '2.2.2', '2.3.1', true, false, false, false, false, false],
            [false, '2.2.2', '2.3.1', true, false, false, true, false, false],
            [false, '2.2.2', '2.3.1', true, false, false, true, true, false],
            [false, '2.2.2', '2.3.1', true, true, false, false, false, false],
            [false, '2.2.2', '2.3.1', true, true, false, true, false, false],
            [false, '2.2.2', '2.3.1', true, true, false, true, true, false],
            [false, '2.2.2', '2.3.1', true, true, true, false, false, false],
            [false, '2.2.2', '2.3.1', true, true, true, true, false, false],
            [false, '2.2.2', '2.3.1', true, true, true, true, true, false],
            [false, '2.2.2', '2.3.2', false, false, false, false, false, false],
            [false, '2.2.2', '2.3.2', false, false, false, true, false, false],
            [false, '2.2.2', '2.3.2', false, false, false, true, true, false],
            [false, '2.2.2', '2.3.2', false, true, false, false, false, false],
            [false, '2.2.2', '2.3.2', false, true, false, true, false, false],
            [false, '2.2.2', '2.3.2', false, true, false, true, true, false],
            [false, '2.2.2', '2.3.2', false, true, true, false, false, false],
            [false, '2.2.2', '2.3.2', false, true, true, true, false, false],
            [false, '2.2.2', '2.3.2', false, true, true, true, true, false],
            [false, '2.2.2', '2.3.2', true, false, false, false, false, false],
            [false, '2.2.2', '2.3.2', true, false, false, true, false, false],
            [false, '2.2.2', '2.3.2', true, false, false, true, true, false],
            [false, '2.2.2', '2.3.2', true, true, false, false, false, false],
            [false, '2.2.2', '2.3.2', true, true, false, true, false, false],
            [false, '2.2.2', '2.3.2', true, true, false, true, true, false],
            [false, '2.2.2', '2.3.2', true, true, true, false, false, false],
            [false, '2.2.2', '2.3.2', true, true, true, true, false, false],
            [false, '2.2.2', '2.3.2', true, true, true, true, true, false],
            [true, '2.2.2', '1.3.2', false, false, false, false, false, false],
            [true, '2.2.2', '1.3.2', false, false, false, true, false, false],
            [true, '2.2.2', '1.3.2', false, false, false, true, true, false],
            [true, '2.2.2', '1.3.2', false, true, false, false, false, false],
            [true, '2.2.2', '1.3.2', false, true, false, true, false, false],
            [true, '2.2.2', '1.3.2', false, true, false, true, true, false],
            [true, '2.2.2', '1.3.2', false, true, true, false, false, false],
            [true, '2.2.2', '1.3.2', false, true, true, true, false, false],
            [true, '2.2.2', '1.3.2', false, true, true, true, true, false],
            [true, '2.2.2', '1.3.2', true, false, false, false, false, false],
            [true, '2.2.2', '1.3.2', true, false, false, true, false, false],
            [true, '2.2.2', '1.3.2', true, false, false, true, true, false],
            [true, '2.2.2', '1.3.2', true, true, false, false, false, false],
            [true, '2.2.2', '1.3.2', true, true, false, true, false, false],
            [true, '2.2.2', '1.3.2', true, true, false, true, true, false],
            [true, '2.2.2', '1.3.2', true, true, true, false, false, false],
            [true, '2.2.2', '1.3.2', true, true, true, true, false, false],
            [true, '2.2.2', '1.3.2', true, true, true, true, true, false],
            [true, '2.2.2', '2.1.3', false, false, false, false, false, false],
            [true, '2.2.2', '2.1.3', false, false, false, true, false, false],
            [true, '2.2.2', '2.1.3', false, false, false, true, true, false],
            [true, '2.2.2', '2.1.3', false, true, false, false, false, false],
            [true, '2.2.2', '2.1.3', false, true, false, true, false, false],
            [true, '2.2.2', '2.1.3', false, true, false, true, true, false],
            [true, '2.2.2', '2.1.3', false, true, true, false, false, false],
            [true, '2.2.2', '2.1.3', false, true, true, true, false, false],
            [true, '2.2.2', '2.1.3', false, true, true, true, true, false],
            [true, '2.2.2', '2.1.3', true, false, false, false, false, false],
            [true, '2.2.2', '2.1.3', true, false, false, true, false, false],
            [true, '2.2.2', '2.1.3', true, false, false, true, true, false],
            [true, '2.2.2', '2.1.3', true, true, false, false, false, false],
            [true, '2.2.2', '2.1.3', true, true, false, true, false, false],
            [true, '2.2.2', '2.1.3', true, true, false, true, true, false],
            [true, '2.2.2', '2.1.3', true, true, true, false, false, false],
            [true, '2.2.2', '2.1.3', true, true, true, true, false, false],
            [true, '2.2.2', '2.1.3', true, true, true, true, true, false],
            [true, '2.2.2', '2.2.1', false, false, false, false, false, false],
            [true, '2.2.2', '2.2.1', false, false, false, true, false, false],
            [true, '2.2.2', '2.2.1', false, false, false, true, true, false],
            [true, '2.2.2', '2.2.1', false, true, false, false, false, false],
            [true, '2.2.2', '2.2.1', false, true, false, true, false, false],
            [true, '2.2.2', '2.2.1', false, true, false, true, true, false],
            [true, '2.2.2', '2.2.1', false, true, true, false, false, false],
            [true, '2.2.2', '2.2.1', false, true, true, true, false, false],
            [true, '2.2.2', '2.2.1', false, true, true, true, true, false],
            [true, '2.2.2', '2.2.1', true, false, false, false, false, false],
            [true, '2.2.2', '2.2.1', true, false, false, true, false, false],
            [true, '2.2.2', '2.2.1', true, false, false, true, true, false],
            [true, '2.2.2', '2.2.1', true, true, false, false, false, false],
            [true, '2.2.2', '2.2.1', true, true, false, true, false, false],
            [true, '2.2.2', '2.2.1', true, true, false, true, true, false],
            [true, '2.2.2', '2.2.1', true, true, true, false, false, false],
            [true, '2.2.2', '2.2.1', true, true, true, true, false, false],
            [true, '2.2.2', '2.2.1', true, true, true, true, true, false],
            [true, '2.2.2', '2.2.2', false, false, false, false, false, false],
            [true, '2.2.2', '2.2.2', false, false, false, true, false, false],
            [true, '2.2.2', '2.2.2', false, false, false, true, true, false],
            [true, '2.2.2', '2.2.2', false, true, false, false, false, false],
            [true, '2.2.2', '2.2.2', false, true, false, true, false, false],
            [true, '2.2.2', '2.2.2', false, true, false, true, true, false],
            [true, '2.2.2', '2.2.2', false, true, true, false, false, false],
            [true, '2.2.2', '2.2.2', false, true, true, true, false, false],
            [true, '2.2.2', '2.2.2', false, true, true, true, true, false],
            [true, '2.2.2', '2.2.2', true, false, false, false, false, false],
            [true, '2.2.2', '2.2.2', true, false, false, true, false, false],
            [true, '2.2.2', '2.2.2', true, false, false, true, true, false],
            [true, '2.2.2', '2.2.2', true, true, false, false, false, false],
            [true, '2.2.2', '2.2.2', true, true, false, true, false, false],
            [true, '2.2.2', '2.2.2', true, true, false, true, true, false],
            [true, '2.2.2', '2.2.2', true, true, true, false, false, false],
            [true, '2.2.2', '2.2.2', true, true, true, true, false, false],
            [true, '2.2.2', '2.2.2', true, true, true, true, true, false],
            [true, '2.2.2', '2.2.3', false, false, false, false, false, false],
            [true, '2.2.2', '2.2.3', false, false, false, true, false, false],
            [true, '2.2.2', '2.2.3', false, false, false, true, true, false],
            [true, '2.2.2', '2.2.3', false, true, false, false, false, false],
            [true, '2.2.2', '2.2.3', false, true, false, true, false, false],
            [true, '2.2.2', '2.2.3', false, true, false, true, true, false],
            [true, '2.2.2', '2.2.3', false, true, true, false, false, false],
            [true, '2.2.2', '2.2.3', false, true, true, true, false, false],
            [true, '2.2.2', '2.2.3', false, true, true, true, true, false],
            [true, '2.2.2', '2.2.3', true, false, false, false, false, false],
            [true, '2.2.2', '2.2.3', true, false, false, true, false, false],
            [true, '2.2.2', '2.2.3', true, false, false, true, true, false],
            [true, '2.2.2', '2.2.3', true, true, false, false, false, false],
            [true, '2.2.2', '2.2.3', true, true, false, true, false, false],
            [true, '2.2.2', '2.2.3', true, true, false, true, true, false],
            [true, '2.2.2', '2.2.3', true, true, true, false, false, false],
            [true, '2.2.2', '2.2.3', true, true, true, true, false, false],
            [true, '2.2.2', '2.2.3', true, true, true, true, true, false],
            [true, '2.2.2', '2.3.1', false, false, false, false, false, false],
            [true, '2.2.2', '2.3.1', false, false, false, true, false, false],
            [true, '2.2.2', '2.3.1', false, false, false, true, true, false],
            [true, '2.2.2', '2.3.1', false, true, false, false, false, false],
            [true, '2.2.2', '2.3.1', false, true, false, true, false, false],
            [true, '2.2.2', '2.3.1', false, true, false, true, true, false],
            [true, '2.2.2', '2.3.1', false, true, true, false, false, false],
            [true, '2.2.2', '2.3.1', false, true, true, true, false, false],
            [true, '2.2.2', '2.3.1', false, true, true, true, true, false],
            [true, '2.2.2', '2.3.1', true, false, false, false, false, false],
            [true, '2.2.2', '2.3.1', true, false, false, true, false, false],
            [true, '2.2.2', '2.3.1', true, false, false, true, true, false],
            [true, '2.2.2', '2.3.1', true, true, false, false, false, false],
            [true, '2.2.2', '2.3.1', true, true, false, true, false, false],
            [true, '2.2.2', '2.3.1', true, true, false, true, true, false],
            [true, '2.2.2', '2.3.1', true, true, true, false, false, false],
            [true, '2.2.2', '2.3.1', true, true, true, true, false, false],
            [true, '2.2.2', '2.3.1', true, true, true, true, true, false],
            [true, '2.2.2', '2.3.2', false, false, false, false, false, false],
            [true, '2.2.2', '2.3.2', false, false, false, true, false, false],
            [true, '2.2.2', '2.3.2', false, false, false, true, true, false],
            [true, '2.2.2', '2.3.2', false, true, false, false, false, false],
            [true, '2.2.2', '2.3.2', false, true, false, true, false, false],
            [true, '2.2.2', '2.3.2', false, true, false, true, true, false],
            [true, '2.2.2', '2.3.2', false, true, true, false, false, false],
            [true, '2.2.2', '2.3.2', false, true, true, true, false, false],
            [true, '2.2.2', '2.3.2', false, true, true, true, true, false],
            [true, '2.2.2', '2.3.2', true, false, false, false, false, false],
            [true, '2.2.2', '2.3.2', true, false, false, true, false, false],
            [true, '2.2.2', '2.3.2', true, false, false, true, true, false],
            [true, '2.2.2', '2.3.2', true, true, false, false, false, false],
            [true, '2.2.2', '2.3.2', true, true, false, true, false, false],
            [true, '2.2.2', '2.3.2', true, true, false, true, true, false],
            [true, '2.2.2', '2.3.2', true, true, true, false, false, false],
            [true, '2.2.2', '2.3.2', true, true, true, true, false, false],
            [true, '2.2.2', '2.3.2', true, true, true, true, true, false],
        ];
    }

    /**
     * @test
     * @dataProvider needsConfirmProviderDateNull
     */
    public function it_knows_if_client_app_needs_confirm_when_dates_logic_are_null(
        bool $createdAtDayOlder,
        bool $expected
    ) {
        $versionApp = '2.2.2';
        $appVersion = AppVersion::factory()->create(['current' => $versionApp, 'video_url' => "https://video-url.com"]);

        $createdAtDate = $createdAtDayOlder ? Carbon::yesterday() : Carbon::now();

        $user = User::factory()->create([
            'created_at'                => $createdAtDate,
            'verified_at'               => null,
            'registration_completed_at' => null,
        ]);

        $this->assertSame($expected, $appVersion->needsConfirm($versionApp, $user));
    }

    public function needsConfirmProviderDateNull(): array
    {
        return [
            // createdAtDayOlder, expected
            [true, true],
            [false, false],
        ];
    }

    /**
     * @test
     * @dataProvider needsUpdateProvider
     */
    public function it_knows_if_client_app_needs_update(
        string $minVersion,
        string $clientVersion,
        bool $expected
    ) {
        $appVersion = AppVersion::factory()->create(['min' => $minVersion]);

        $this->assertSame($expected, $appVersion->needsUpdate($clientVersion));
    }

    public function needsUpdateProvider(): array
    {
        return [
            ['2.2.2', '1.3.2', true],
            ['2.2.2', '2.1.3', true],
            ['2.2.2', '2.2.1', true],
            ['2.2.2', '2.2.2', false],
            ['2.2.2', '2.2.3', false],
            ['2.2.2', '2.3.1', false],
            ['2.2.2', '2.3.2', false],
        ];
    }
}
