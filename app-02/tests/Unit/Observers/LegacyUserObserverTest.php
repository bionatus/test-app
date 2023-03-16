<?php

namespace Tests\Unit\Observers;

use App;
use App\Constants\MediaCollectionNames;
use App\Models\User;
use App\Observers\LegacyUserObserver;
use App\Services\Hubspot\Hubspot;
use Config;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Intervention\Image\ImageManager;
use Mockery;
use Spatie\MediaLibrary\Support\PathGenerator\PathGeneratorFactory;
use Storage;
use Tests\TestCase;

class LegacyUserObserverTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_syncs_user_photo_to_media_library_on_creation()
    {
        $diskName = Config::get('media-library.disk_name');
        Storage::fake($diskName);
        Storage::fake('public');
        $image = (new ImageManager())->canvas(800, 600)->encode('jpeg');
        Storage::disk('public')->put('avatar.jpeg', $image);

        $mock = Mockery::mock(Hubspot::class)->makePartial();
        $mock->shouldReceive('createWithApiKey')->withNoArgs()->andReturn($mock);
        $mock->shouldReceive('createContact')->withAnyArgs()->andReturnNull();
        App::bind(Hubspot::class, fn() => $mock);

        $europeUser = new App\User([
            'email'       => 'example@test.com',
            'password'    => 'password',
            'first_name'  => 'John',
            'last_name'   => 'Doe',
            'public_name' => 'JohnDoe1',
            'name'        => 'John Doe',
        ]);
        $europeUser->saveQuietly();

        $europeUser->photo = 'avatar.jpeg';

        $observer = new LegacyUserObserver();
        $observer->created($europeUser);

        $user = User::find($europeUser->getKey());

        $this->assertTrue($user->hasMedia(MediaCollectionNames::IMAGES));
        $this->assertCount(1, $user->getMedia(MediaCollectionNames::IMAGES));

        $media = $user->getFirstMedia(MediaCollectionNames::IMAGES);
        $this->assertSame($europeUser->photo, $media->file_name);
        $pathGenerator = PathGeneratorFactory::create($media);
        Storage::disk($diskName)->assertExists($pathGenerator->getPath($media) . $media->file_name);
    }

    /** @test */
    public function it_syncs_user_photo_to_media_library_on_update()
    {
        $diskName = Config::get('media-library.disk_name');
        Storage::fake($diskName);
        Storage::fake('public');
        $image = (new ImageManager())->canvas(800, 600)->encode('jpeg');
        Storage::disk('public')->put('avatar.jpeg', $image);

        $mock = Mockery::mock(Hubspot::class)->makePartial();
        $mock->shouldReceive('createWithApiKey')->withNoArgs()->andReturn($mock);
        $mock->shouldReceive('upsertContact')->withAnyArgs()->andReturnNull();
        App::bind(Hubspot::class, fn() => $mock);

        $europeUser = new App\User([
            'email'       => 'example@test.com',
            'password'    => 'password',
            'first_name'  => 'John',
            'last_name'   => 'Doe',
            'public_name' => 'JohnDoe1',
            'name'        => 'John Doe',
        ]);
        $europeUser->saveQuietly();

        $europeUser->photo = 'avatar.jpeg';

        $observer = new LegacyUserObserver();
        $observer->updated($europeUser);

        $user = User::find($europeUser->getKey());

        $this->assertTrue($user->hasMedia(MediaCollectionNames::IMAGES));
        $this->assertCount(1, $user->getMedia(MediaCollectionNames::IMAGES));

        $media = $user->getFirstMedia(MediaCollectionNames::IMAGES);
        $this->assertSame($europeUser->photo, $media->file_name);
        $pathGenerator = PathGeneratorFactory::create($media);
        Storage::disk($diskName)->assertExists($pathGenerator->getPath($media) . $media->file_name);
    }

    /** @test */
    public function it_should_not_sync_user_photo_to_media_library_when_the_photo_was_not_updated()
    {
        $mock = Mockery::mock(Hubspot::class);
        $mock->shouldReceive('createWithApiKey')->withNoArgs()->andReturn($mock);
        $mock->shouldReceive('upsertContact')->withAnyArgs()->andReturnNull();
        App::bind(Hubspot::class, fn() => $mock);

        $europeUser = new App\User([
            'email'       => 'example@test.com',
            'password'    => 'password',
            'first_name'  => 'John',
            'last_name'   => 'Doe',
            'public_name' => 'JohnDoe1',
            'name'        => 'John Doe',
            'photo'       => 'avatar.jpeg',
        ]);
        $europeUser->saveQuietly();

        $mock = Mockery::mock(LegacyUserObserver::class);
        $mock->makePartial()->shouldAllowMockingProtectedMethods();
        $mock->shouldNotReceive('syncPhotoToMediaLibrary');
        $mock->updated($europeUser);
    }
}
