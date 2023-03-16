<?php

namespace Tests\Unit\Http\Resources\Api\V3\OrderSupplier;

use App\Constants\MediaCollectionNames;
use App\Http\Resources\Api\V3\OrderSupplier\BaseResource;
use App\Http\Resources\Api\V3\OrderSupplier\SupplierHourResource;
use App\Http\Resources\Models\ImageResource;
use App\Models\ForbiddenZipCode;
use App\Models\Media;
use App\Models\Supplier;
use App\Models\SupplierHour;
use App\Models\SupplierUser;
use App\Models\User;
use Config;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Tests\TestCase;

class BaseResourceTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /** @test */
    public function it_has_correct_fields()
    {
        $supplier = Supplier::factory()->createQuietly();
        SupplierHour::factory()->usingSupplier($supplier)->count(2)->create();

        $diskName = Config::get('media-library.disk_name');
        Storage::fake($diskName);
        Storage::fake('public');
        $image = (new ImageManager())->canvas(800, 600)->encode('jpeg');
        Storage::disk('public')->put('supplier-image.jpeg', $image);
        Storage::disk('public')->put('supplier-logo.jpeg', $image);
        try {
            $supplier->addMediaFromDisk('supplier-image.jpeg', 'public')
                ->toMediaCollection(MediaCollectionNames::IMAGES);
            $supplier->addMediaFromDisk('supplier-logo.jpeg', 'public')->toMediaCollection(MediaCollectionNames::LOGO);
        } catch (Exception $e) {
            // Silently ignored
        }
        /** @var Media $image */
        $image = $supplier->getFirstMedia(MediaCollectionNames::IMAGES);
        /** @var Media $logo */
        $logo = $supplier->getFirstMedia(MediaCollectionNames::LOGO);

        $resource = new BaseResource($supplier);
        $response = $resource->resolve();

        $data = [
            'id'                     => $supplier->getRouteKey(),
            'name'                   => $supplier->name,
            'address'                => $supplier->address,
            'address_2'              => $supplier->address_2,
            'city'                   => $supplier->city,
            'state'                  => $supplier->state,
            'country'                => $supplier->country,
            'zip_code'               => $supplier->zip_code,
            'latitude'               => $supplier->latitude,
            'longitude'              => $supplier->longitude,
            'published'              => !!$supplier->published_at,
            'image'                  => new ImageResource($image),
            'logo'                   => new ImageResource($logo),
            'bluon_live_verified'    => !!($supplier->verified_at && $supplier->published_at),
            'offers_delivery'        => !!$supplier->offers_delivery,
            'favorite'               => false,
            'invitation_sent'        => false,
            'distance'               => $supplier->distance,
            'preferred'              => false,
            'open_hours'             => SupplierHourResource::collection($supplier->supplierHours),
            'can_use_curri_delivery' => false,
        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_preferred()
    {
        $user      = User::factory()->create();
        $preferred = SupplierUser::factory()->usingUser($user)->createQuietly(['preferred' => true]);

        $diskName = Config::get('media-library.disk_name');
        Storage::fake($diskName);
        Storage::fake('public');
        $image = (new ImageManager())->canvas(800, 600)->encode('jpeg');
        Storage::disk('public')->put('supplier-image.jpeg', $image);
        Storage::disk('public')->put('supplier-logo.jpeg', $image);
        try {
            $preferred->supplier->addMediaFromDisk('supplier-image.jpeg', 'public')
                ->toMediaCollection(MediaCollectionNames::IMAGES);
            $preferred->supplier->addMediaFromDisk('supplier-logo.jpeg', 'public')
                ->toMediaCollection(MediaCollectionNames::LOGO);
        } catch (Exception $e) {
            // Silently ignored
        }
        /** @var Media $image */
        $image = $preferred->supplier->getFirstMedia(MediaCollectionNames::IMAGES);
        /** @var Media $logo */
        $logo = $preferred->supplier->getFirstMedia(MediaCollectionNames::LOGO);

        $resource = new BaseResource($preferred->supplier);
        $response = $resource->resolve();

        $data = [
            'id'                     => $preferred->supplier->getRouteKey(),
            'name'                   => $preferred->supplier->name,
            'address'                => $preferred->supplier->address,
            'address_2'              => $preferred->supplier->address_2,
            'city'                   => $preferred->supplier->city,
            'state'                  => $preferred->supplier->state,
            'country'                => $preferred->supplier->country,
            'zip_code'               => $preferred->supplier->zip_code,
            'latitude'               => $preferred->supplier->latitude,
            'longitude'              => $preferred->supplier->longitude,
            'published'              => !!$preferred->supplier->published_at,
            'image'                  => new ImageResource($image),
            'logo'                   => new ImageResource($logo),
            'bluon_live_verified'    => !!($preferred->supplier->verified_at && $preferred->supplier->published_at),
            'offers_delivery'        => !!$preferred->supplier->offers_delivery,
            'favorite'               => false,
            'invitation_sent'        => false,
            'distance'               => $preferred->supplier->distance,
            'preferred'              => $preferred->preferred_supplier,
            'open_hours'             => SupplierHourResource::collection(new Collection()),
            'can_use_curri_delivery' => false,
        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_should_return_true_on_bluon_live_verified_if_supplier_is_verified_and_published()
    {
        $supplier = Supplier::factory()->verified()->published()->createQuietly();

        $resource = new BaseResource($supplier);
        $response = $resource->resolve();

        $data = [
            'id'                     => $supplier->getRouteKey(),
            'name'                   => $supplier->name,
            'address'                => $supplier->address,
            'address_2'              => $supplier->address_2,
            'city'                   => $supplier->city,
            'state'                  => $supplier->state,
            'country'                => $supplier->country,
            'zip_code'               => $supplier->zip_code,
            'latitude'               => $supplier->latitude,
            'longitude'              => $supplier->longitude,
            'published'              => !!$supplier->published_at,
            'image'                  => null,
            'logo'                   => null,
            'bluon_live_verified'    => true,
            'offers_delivery'        => !!$supplier->offers_delivery,
            'favorite'               => false,
            'invitation_sent'        => false,
            'distance'               => $supplier->distance,
            'preferred'              => false,
            'open_hours'             => SupplierHourResource::collection(new Collection()),
            'can_use_curri_delivery' => false,
        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_should_not_return_true_on_bluon_live_verified_if_supplier_is_not_verified()
    {
        $supplier = Supplier::factory()->unverified()->published()->createQuietly();

        $resource = new BaseResource($supplier);
        $response = $resource->resolve();

        $data = [
            'id'                     => $supplier->getRouteKey(),
            'name'                   => $supplier->name,
            'address'                => $supplier->address,
            'address_2'              => $supplier->address_2,
            'city'                   => $supplier->city,
            'state'                  => $supplier->state,
            'country'                => $supplier->country,
            'zip_code'               => $supplier->zip_code,
            'latitude'               => $supplier->latitude,
            'longitude'              => $supplier->longitude,
            'published'              => !!$supplier->published_at,
            'image'                  => null,
            'logo'                   => null,
            'bluon_live_verified'    => false,
            'offers_delivery'        => !!$supplier->offers_delivery,
            'favorite'               => false,
            'invitation_sent'        => false,
            'distance'               => $supplier->distance,
            'preferred'              => false,
            'open_hours'             => SupplierHourResource::collection(new Collection()),
            'can_use_curri_delivery' => false,
        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_should_not_return_true_on_bluon_live_verified_if_supplier_is_not_published()
    {
        $supplier = Supplier::factory()->verified()->unpublished()->createQuietly();

        $resource = new BaseResource($supplier);
        $response = $resource->resolve();

        $data = [
            'id'                     => $supplier->getRouteKey(),
            'name'                   => $supplier->name,
            'address'                => $supplier->address,
            'address_2'              => $supplier->address_2,
            'city'                   => $supplier->city,
            'state'                  => $supplier->state,
            'country'                => $supplier->country,
            'zip_code'               => $supplier->zip_code,
            'latitude'               => $supplier->latitude,
            'longitude'              => $supplier->longitude,
            'published'              => !!$supplier->published_at,
            'image'                  => null,
            'logo'                   => null,
            'bluon_live_verified'    => false,
            'offers_delivery'        => !!$supplier->offers_delivery,
            'favorite'               => false,
            'invitation_sent'        => false,
            'distance'               => $supplier->distance,
            'preferred'              => false,
            'open_hours'             => SupplierHourResource::collection(new Collection()),
            'can_use_curri_delivery' => false,
        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_display_sorted_supplier_hours()
    {
        Carbon::setTestNow(Carbon::parse('wednesday'));

        $timezone       = 'America/Los_Angeles';
        $supplier       = Supplier::factory()->createQuietly(['timezone' => $timezone]);
        $hours          = ['from' => '9:00 am', 'to' => '5:00 pm'];
        $supplierHourM  = SupplierHour::factory()->usingSupplier($supplier)->monday()->create($hours);
        $supplierHourTu = SupplierHour::factory()->usingSupplier($supplier)->tuesday()->create($hours);
        $supplierHourW  = SupplierHour::factory()->usingSupplier($supplier)->wednesday()->create($hours);
        $supplierHourTh = SupplierHour::factory()->usingSupplier($supplier)->thursday()->create($hours);
        $supplierHourF  = SupplierHour::factory()->usingSupplier($supplier)->friday()->create($hours);
        $supplierHourSa = SupplierHour::factory()->usingSupplier($supplier)->saturday()->create($hours);

        $sortedSupplierHours = Collection::make([]);
        $sortedSupplierHours->push($supplierHourW)
            ->push($supplierHourTh)
            ->push($supplierHourF)
            ->push($supplierHourSa)
            ->push($supplierHourM)
            ->push($supplierHourTu);

        $resource = new BaseResource($supplier);
        $response = $resource->resolve();
        $data     = json_decode(json_encode($response));

        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, $data);

        $openHours = Collection::make($data->open_hours);
        $openHours->each(function($openHour, $index) use ($timezone, $supplier, $sortedSupplierHours) {
            $supplierHour = $sortedSupplierHours->get($index);
            $dayName      = $supplierHour->day;
            $utcFrom      = Carbon::parse($dayName . ' ' . $supplierHour->from, $timezone)->utc()->toISOString();
            $utcTo        = Carbon::parse($dayName . ' ' . $supplierHour->to, $timezone)->utc()->toISOString();

            $this->assertEquals($utcFrom, $openHour->from);
            $this->assertEquals($utcTo, $openHour->to);
            $this->assertTrue($openHour->from <= $openHour->to);
        });
    }

    /** @test */
    public function it_should_get_can_use_curri_delivery_with_true_if_supplier_has_a_valid_zip_code()
    {
        $supplier = Supplier::factory()->createQuietly(['zip_code' => '12345']);
        SupplierHour::factory()->usingSupplier($supplier)->count(2)->create();

        $diskName = Config::get('media-library.disk_name');
        Storage::fake($diskName);
        Storage::fake('public');
        $image = (new ImageManager())->canvas(800, 600)->encode('jpeg');
        Storage::disk('public')->put('supplier-image.jpeg', $image);
        Storage::disk('public')->put('supplier-logo.jpeg', $image);
        try {
            $supplier->addMediaFromDisk('supplier-image.jpeg', 'public')
                ->toMediaCollection(MediaCollectionNames::IMAGES);
            $supplier->addMediaFromDisk('supplier-logo.jpeg', 'public')->toMediaCollection(MediaCollectionNames::LOGO);
        } catch (Exception $e) {
            // Silently ignored
        }
        /** @var Media $image */
        $image = $supplier->getFirstMedia(MediaCollectionNames::IMAGES);
        /** @var Media $logo */
        $logo = $supplier->getFirstMedia(MediaCollectionNames::LOGO);

        $resource = new BaseResource($supplier);
        $response = $resource->resolve();

        $data = [
            'id'                     => $supplier->getRouteKey(),
            'name'                   => $supplier->name,
            'address'                => $supplier->address,
            'address_2'              => $supplier->address_2,
            'city'                   => $supplier->city,
            'state'                  => $supplier->state,
            'country'                => $supplier->country,
            'zip_code'               => $supplier->zip_code,
            'latitude'               => $supplier->latitude,
            'longitude'              => $supplier->longitude,
            'published'              => !!$supplier->published_at,
            'image'                  => new ImageResource($image),
            'logo'                   => new ImageResource($logo),
            'bluon_live_verified'    => !!($supplier->verified_at && $supplier->published_at),
            'offers_delivery'        => !!$supplier->offers_delivery,
            'favorite'               => false,
            'invitation_sent'        => false,
            'distance'               => $supplier->distance,
            'preferred'              => false,
            'open_hours'             => SupplierHourResource::collection($supplier->supplierHours),
            'can_use_curri_delivery' => true,
        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_should_get_can_use_curri_delivery_with_false_if_supplier_has_not_a_valid_zip_code()
    {
        ForbiddenZipCode::factory()->create(['zip_code' => '11111']);
        $supplier = Supplier::factory()->createQuietly(['zip_code' => '11111']);
        SupplierHour::factory()->usingSupplier($supplier)->count(2)->create();

        $diskName = Config::get('media-library.disk_name');
        Storage::fake($diskName);
        Storage::fake('public');
        $image = (new ImageManager())->canvas(800, 600)->encode('jpeg');
        Storage::disk('public')->put('supplier-image.jpeg', $image);
        Storage::disk('public')->put('supplier-logo.jpeg', $image);
        try {
            $supplier->addMediaFromDisk('supplier-image.jpeg', 'public')
                ->toMediaCollection(MediaCollectionNames::IMAGES);
            $supplier->addMediaFromDisk('supplier-logo.jpeg', 'public')->toMediaCollection(MediaCollectionNames::LOGO);
        } catch (Exception $e) {
            // Silently ignored
        }
        /** @var Media $image */
        $image = $supplier->getFirstMedia(MediaCollectionNames::IMAGES);
        /** @var Media $logo */
        $logo = $supplier->getFirstMedia(MediaCollectionNames::LOGO);

        $resource = new BaseResource($supplier);
        $response = $resource->resolve();

        $data = [
            'id'                     => $supplier->getRouteKey(),
            'name'                   => $supplier->name,
            'address'                => $supplier->address,
            'address_2'              => $supplier->address_2,
            'city'                   => $supplier->city,
            'state'                  => $supplier->state,
            'country'                => $supplier->country,
            'zip_code'               => $supplier->zip_code,
            'latitude'               => $supplier->latitude,
            'longitude'              => $supplier->longitude,
            'published'              => !!$supplier->published_at,
            'image'                  => new ImageResource($image),
            'logo'                   => new ImageResource($logo),
            'bluon_live_verified'    => !!($supplier->verified_at && $supplier->published_at),
            'offers_delivery'        => !!$supplier->offers_delivery,
            'favorite'               => false,
            'invitation_sent'        => false,
            'distance'               => $supplier->distance,
            'preferred'              => false,
            'open_hours'             => SupplierHourResource::collection($supplier->supplierHours),
            'can_use_curri_delivery' => false,
        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
