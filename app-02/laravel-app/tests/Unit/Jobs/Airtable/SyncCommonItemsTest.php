<?php

namespace Tests\Unit\Jobs\Airtable;

use App\Jobs\Airtable\SyncCommonItems;
use App\Lib\AirtableClient;
use App\Mail\CommonItem\SyncCommonItemReportEmail;
use App\Models\Supply;
use App\Models\SupplyCategory;
use Config;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Mail;
use Mockery;
use Tests\TestCase;

class SyncCommonItemsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_fetch_a_table_from_airtable()
    {
        $client = Mockery::mock(AirtableClient::class);
        $client->shouldReceive('setEndpoint')->withArgs([null])->once();
        $job = new SyncCommonItems();
        $client->shouldReceive('prepare')->withArgs([[$job, 'parseResponse']])->once();
        $job->handle($client);
    }

    /** @test */
    public function it_does_not_store_records_without_a_name()
    {
        $response = [[]];

        $job = new SyncCommonItems();
        $job->parseResponse($response);

        $this->assertDatabaseCount(Supply::tableName(), 0);
    }

    /** @test */
    public function it_does_not_store_records_with_id_but_without_fields()
    {
        $response = [['id' => 'no fields']];

        $job = new SyncCommonItems();
        $job->parseResponse($response);

        $this->assertDatabaseCount(Supply::tableName(), 0);
    }

    /** @test */
    public function it_does_not_store_records_without_an_airtable_id_field()
    {
        $response = [
            [
                'id'     => 'no airtable_id',
                'fields' => [],
            ],
        ];

        $job = new SyncCommonItems();
        $job->parseResponse($response);

        $this->assertDatabaseCount(Supply::tableName(), 0);
    }

    /** @test */
    public function it_does_not_store_records_without_an_item_field()
    {
        $response = [
            [
                'id'     => 'no item',
                'fields' => [
                    'item'       => null,
                    'category_1' => 'category 1',
                ],
            ],
        ];

        $job = new SyncCommonItems();
        $job->parseResponse($response);

        $this->assertDatabaseCount(Supply::tableName(), 0);
    }

    /** @test */
    public function it_does_not_store_records_without_a_category()
    {
        $response = [
            [
                'id'     => 'no-id',
                'fields' => [
                    'item'   => 'ItemTest',
                    'Status' => 'live',
                ],
            ],
        ];

        $job = new SyncCommonItems();
        $job->parseResponse($response);

        $this->assertDatabaseCount(Supply::tableName(), 0);
    }

    /** @test */
    public function it_store_new_records()
    {
        $response = [
            [
                'id'     => 'valid',
                'fields' => [
                    'item'       => $name = 'Name item Lorem',
                    'category_1' => 'This is a Category',
                    'Status'     => 'live',
                ],
            ],
        ];

        $job = new SyncCommonItems();
        $job->parseResponse($response);

        $this->assertDatabaseCount(Supply::tableName(), 1);
        $this->assertDatabaseHas(Supply::tableName(), [
            'name'          => $name,
            'internal_name' => $name,
        ]);
    }

    /** @test */
    public function it_updates_existing_records()
    {
        Supply::factory()->create([
            'name' => 'Old Supply',
        ]);
        Supply::flushEventListeners();

        $response = [
            [
                'id'     => 'existing',
                'fields' => [
                    'item'       => $name = 'Old Supply',
                    'Status'     => 'live',
                    'category_1' => $category = 'New Category',
                ],
            ],
        ];

        $job = new SyncCommonItems();
        $job->parseResponse($response);

        $this->assertDatabaseCount(Supply::tableName(), 1);
        $this->assertDatabaseHas(Supply::tableName(), [
            'name'          => $name,
            'internal_name' => $name,
        ]);
        $this->assertDatabaseHas(SupplyCategory::tableName(), [
            'name' => $category,
        ]);
    }

    /** @test */
    public function it_ignores_duplicate_airtable_name_records()
    {
        $response = [
            [
                'id'     => 'existing',
                'fields' => [
                    'item'       => $name = 'Old Supply',
                    'Status'     => 'live',
                    'category_1' => $category = 'New Category',
                ],
            ],
            [
                'id'     => 'existing',
                'fields' => [
                    'item'       => $name,
                    'Status'     => 'live',
                    'category_1' => $category,
                ],
            ],
        ];

        $job = new SyncCommonItems();
        $job->parseResponse($response);

        $this->assertDatabaseCount(Supply::tableName(), 1);

        $this->assertDatabaseHas(Supply::tableName(), [
            'name' => $name,
        ]);
        $this->assertDatabaseHas(SupplyCategory::tableName(), [
            'name' => $category,
        ]);
    }

    /** @test */
    public function it_ignores_duplicate_airtable_category_records()
    {
        $response = [
            [
                'id'     => 'existing',
                'fields' => [
                    'item'       => 'First CommonItem',
                    'Status'     => 'live',
                    'category_1' => $category = 'Category One',
                ],
            ],
            [
                'id'     => 'existing2',
                'fields' => [
                    'item'       => 'Second CommonItem',
                    'Status'     => 'live',
                    'category_1' => $category,
                ],
            ],
            [
                'id'     => 'existing3',
                'fields' => [
                    'item'       => 'Third CommonItem',
                    'Status'     => 'live',
                    'category_1' => $category,
                ],
            ],
        ];

        $job = new SyncCommonItems();
        $job->parseResponse($response);

        $this->assertDatabaseCount(Supply::tableName(), 3);

        $this->assertDatabaseCount(SupplyCategory::tableName(), 1);
        $this->assertDatabaseHas(SupplyCategory::tableName(), [
            'name' => $category,
        ]);
    }

    /** @test */
    public function it_store_records_with_three_categories()
    {
        $response = [
            [
                'id'     => 'existing1',
                'fields' => [
                    'item'       => 'Third CommonItem',
                    'Status'     => 'live',
                    'category_1' => $category_1 = 'Category one',
                    'category_2' => $category_2 = 'Category two',
                    'category_3' => $group = 'Category Group',
                ],
            ],
        ];

        $job = new SyncCommonItems();
        $job->parseResponse($response);

        $this->assertDatabaseCount(Supply::tableName(), 1);

        $this->assertDatabaseCount(SupplyCategory::tableName(), 2);
        $this->assertDatabaseHas(SupplyCategory::tableName(), [
            'name' => $category_1,
        ]);
        $this->assertDatabaseHas(SupplyCategory::tableName(), [
            'name' => $category_2,
        ]);
    }

    /** @test */
    public function it_does_not_store_records_with_a_name_of_more_than_255_characters()
    {
        $response = [
            [
                'id'     => 'existing1',
                'fields' => [
                    'item'       => Str::random(256),
                    'Status'     => 'live',
                    'category_1' => 'Category one',
                    'category_2' => 'Category two',
                    'category_3' => 'Category three',
                ],
            ],
        ];

        $job = new SyncCommonItems();
        $job->parseResponse($response);

        $this->assertDatabaseCount(Supply::tableName(), 0);
    }

    /** @test */
    public function it_does_not_store_records_with_a_category_name_of_more_than_255_characters()
    {
        $response = [
            [
                'id'     => 'existing1',
                'fields' => [
                    'item'       => 'Regular Item',
                    'Status'     => 'live',
                    'category_1' => Str::random(256),
                    'category_2' => 'Category two',
                    'category_3' => 'Category three',
                ],
            ],
        ];

        $job = new SyncCommonItems();
        $job->parseResponse($response);

        $this->assertDatabaseCount(Supply::tableName(), 0);
        $this->assertDatabaseCount(SupplyCategory::tableName(), 0);
    }

    /** @test */
    public function it_sends_an_report_email()
    {
        Config::set('mail.reports.sync', 'reports@email.com');
        Mail::fake();

        $client = Mockery::mock(AirtableClient::class);
        $client->shouldReceive('setEndpoint')->withArgs([null])->once();
        $job = new SyncCommonItems();
        $job->parseResponse([
            [
                'id'     => 'existing1',
                'fields' => [
                    'item'       => 'Third CommonItem',
                    'Status'     => 'live',
                    'category_1' => 'Category one',
                    'category_2' => 'Category two',
                    'category_3' => 'Category three',
                ],
            ],
        ]);
        $client->shouldReceive('prepare')->withArgs([[$job, 'parseResponse']])->once();
        $job->handle($client);

        Mail::assertQueued(SyncCommonItemReportEmail::class);
    }
}
