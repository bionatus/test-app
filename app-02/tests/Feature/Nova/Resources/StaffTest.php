<?php

namespace Tests\Feature\Nova\Resources;

use App;
use App\Models\Scopes\ByRouteKey;
use App\Models\Setting;
use App\Models\SettingStaff;
use App\Models\Staff;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\Nova\TestCase;

/** @see \App\Nova\Resources\Staff */
class StaffTest extends TestCase
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
    public function it_displays_a_list_of_counter_staff()
    {
        $supplier  = Supplier::factory()->createQuietly();
        $staffList = Staff::factory()->usingSupplier($supplier)->counter()->count(10)->create();
        $response  = $this->getJson($this->path);
        $response->assertStatus(Response::HTTP_OK);
        $this->assertCount($response->json('total'), $staffList);

        $data = Collection::make($response->json('resources'));

        $firstPageStaffList = $staffList->sortByDesc('id')->values()->take(count($data));

        $this->assertEquals($data->pluck('id.value'), $firstPageStaffList->pluck('id'),
            'Resources are not in the correct order.');
    }

    /** @test */
    public function it_creates_a_counter_staff()
    {
        $supplier = Supplier::factory()->createQuietly();
        $response = $this->postJson($this->path, [
            'name'               => $name = 'A new staff',
            'email'              => $email = 'staff@email.com',
            'phone'              => $phone = '123456',
            'sms_notification'   => true,
            'email_notification' => true,
            'viaResource'        => $supplier->tableName(),
            'viaResourceId'      => $supplier->getKey(),
            'viaRelationship'    => 'counters',
        ]);

        $response->assertJsonMissingValidationErrors();
        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseHas(Staff::tableName(), ['name' => $name, 'email' => $email, 'phone' => $phone]);

        $settingSmsId = Setting::scoped(new ByRouteKey(Setting::SLUG_STAFF_SMS_NOTIFICATION))->first()->getKey();
        $this->assertDatabaseHas(SettingStaff::tableName(), [
            'staff_id'   => $response->json('resource.id'),
            'setting_id' => $settingSmsId,
            'value'      => true,
        ]);

        $settingEmailId = Setting::scoped(new ByRouteKey(Setting::SLUG_STAFF_EMAIL_NOTIFICATION))->first()->getKey();
        $this->assertDatabaseHas(SettingStaff::tableName(), [
            'staff_id'   => $response->json('resource.id'),
            'setting_id' => $settingEmailId,
            'value'      => true,
        ]);
    }

    /** @test * */
    public function a_counter_staff_can_be_retrieved_with_correct_resource_elements()
    {
        $supplier = Supplier::factory()->createQuietly();
        $staff    = Staff::factory()->usingSupplier($supplier)->counter()->create();

        $response = $this->getJson($this->path . $staff->getKey());

        $response->assertStatus(Response::HTTP_OK);

        $staffFields            = [
            [
                'component' => 'id-field',
                'attribute' => 'id',
                'value'     => $staff->getKey(),
            ],
            [
                'component' => 'text-field',
                'attribute' => 'name',
                'value'     => $staff->name,
            ],
            [
                'component' => 'text-field',
                'attribute' => 'email',
                'value'     => $staff->email,
            ],
            [
                'component' => 'text-field',
                'attribute' => 'phone',
                'value'     => $staff->phone,
            ],

        ];
        $smsNotificationField   = [
            [
                'component' => 'text-field',
                'attribute' => 'sms_notification',
                'value'     => true,
            ],
        ];
        $emailNotificationField = [
            [
                'component' => 'text-field',
                'attribute' => 'email_notification',
                'value'     => true,
            ],
        ];
        $fields                 = array_merge($staffFields, $smsNotificationField, $emailNotificationField);

        $this->assertCount(count($fields), $response->json('resource.fields'));

        $response->assertJson([
            'title'    => $staff->name,
            'resource' => [
                'id'     => [
                    'value' => $staff->getKey(),
                ],
                'fields' => $staffFields,
            ],
        ]);
    }

    /** @test */
    public function it_updates_a_counter_staff()
    {
        $supplier = Supplier::factory()->createQuietly();
        $staff    = Staff::factory()->usingSupplier($supplier)->counter()->create();

        $fieldsToUpdate = Collection::make([
            'name'               => 'A name',
            'email'              => 'staff@email.com',
            'phone'              => '123456',
            'sms_notification'   => false,
            'email_notification' => false,
        ]);

        $response = $this->putJson($this->path . $staff->getKey(), $fieldsToUpdate->toArray());
        $response->assertJsonMissingValidationErrors();
        $response->assertStatus(Response::HTTP_OK);

        $staffFields      = $fieldsToUpdate->forget('sms_notification')
            ->forget('email_notification')
            ->put('id', $staff->getKey())
            ->toArray();
        $settingSmsId     = Setting::scoped(new ByRouteKey(Setting::SLUG_STAFF_SMS_NOTIFICATION))->first()->getKey();
        $smsSettingFields = [
            'staff_id'   => $staff->getKey(),
            'setting_id' => $settingSmsId,
            'value'      => false,
        ];

        $settingEmailId     = Setting::scoped(new ByRouteKey(Setting::SLUG_STAFF_EMAIL_NOTIFICATION))
            ->first()
            ->getKey();
        $emailSettingFields = [
            'staff_id'   => $staff->getKey(),
            'setting_id' => $settingEmailId,
            'value'      => false,
        ];

        $this->assertDatabaseHas(Staff::tableName(), $staffFields);
        $this->assertDatabaseHas(SettingStaff::tableName(), $smsSettingFields);
        $this->assertDatabaseHas(SettingStaff::tableName(), $emailSettingFields);
    }

    /** @test */
    public function it_destroy_a_counter_staff()
    {
        $supplier = Supplier::factory()->createQuietly();
        $staff    = Staff::factory()->usingSupplier($supplier)->counter()->create();

        $response = $this->deleteJson($this->path . '?resources[]=' . $staff->getKey());

        $response->assertStatus(Response::HTTP_OK);

        $this->assertModelMissing($staff);
    }
}
