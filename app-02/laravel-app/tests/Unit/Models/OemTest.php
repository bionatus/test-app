<?php

namespace Tests\Unit\Models;

use App\Models\Oem;
use App\Models\OemPart;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class OemTest extends ModelTestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(Oem::tableName(), [
            'id',
            'uuid',
            'status',
            'series_id',
            'model_type_id',
            'model',
            'logo',
            'unit_image',
            'new_system_type',
            'forum_tag',
            'model_description',
            'model_notes',
            'show_parts',
            'service_facts',
            'product_data',
            'iom',
            'controls_manuals',
            'bluon_guidelines',
            'diagnostic',
            'wiring_diagram',
            'misc',
            'nomenclature',
            'tonnage',
            'total_circuits',
            'dx_chiller',
            'cooling_type',
            'heating_type',
            'seer',
            'eer',
            'refrigerant',
            'original_charge_oz',
            'compressor_brand',
            'compressor_type',
            'compressor_model',
            'total_compressors',
            'compressors_per_circuit',
            'compressor_sizes',
            'rla',
            'lra',
            'capacity_staging',
            'lowest_staging',
            'compressor_notes',
            'oil_type',
            'oil_amt_oz',
            'oil_notes',
            'device_type',
            'devices_per_circuit',
            'total_devices',
            'device_size',
            'metering_device_notes',
            'fan_type',
            'cfm_range',
            'fan_notes',
            'voltage_phase_hz',
            'standard_controls',
            'optional_controls',
            'conversion_job',
            'warnings',
            'bid_type',
            'man_hours',
            'charge_lbs',
            'exv',
            'exv_qty',
            'exv_2',
            'exv_2_qty',
            'control_panel',
            'inspect',
            'baseline',
            'airflow_waterflow',
            'recover',
            'controls',
            'replace_install',
            'leak_check',
            'evacuate',
            'charge',
            'tune',
            'verify',
            'label',
            'conversion_notes',
            'date_added',
            'qa',
            'qa_qc_comments',
            'last_qc_date',
            'info_source',
            'source',
            'match',
            'syncing_notes',
            'parts_manuals',
            'cooling_btuh',
            'heating_btuh',
            'call_group_tags',
            'calling_groups',
            'created_at',
            'updated_at',
        ]);
    }

    /** @test */
    public function it_uses_uuid()
    {
        $oem = Oem::factory()->create(['uuid' => Str::uuid()->toString()]);

        $this->assertEquals($oem->uuid, $oem->getRouteKey());
    }

    /** @test */
    public function it_fills_uuid_on_creation()
    {
        $oem = Oem::factory()->make(['uuid' => null]);
        $oem->save();

        $this->assertNotNull($oem->uuid);
    }

    /** @test */
    public function it_knows_its_functional_parts_count()
    {
        $oem = Oem::factory()->create();
        OemPart::factory()->usingOem($oem)->count($partsCount = 10)->create();
        OemPart::factory()->count(5)->create();

        $this->assertSame($partsCount, $oem->functionalPartsCount());
    }

    /** @test */
    public function it_knows_its_manuals_count()
    {
        $oem0 = Oem::factory()->create([
            Oem::MANUAL_TYPE_IOM        => '',
            Oem::MANUAL_TYPE_DIAGNOSTIC => null,
        ]);
        $oem1 = Oem::factory()->create([
            Oem::MANUAL_TYPE_IOM => ' https://server.domain/file.pdf',
        ]);
        $oem2 = Oem::factory()->create([
            Oem::MANUAL_TYPE_IOM => 'https://server.domain/file.pdf; http://server.domain/file.pdf;',
        ]);
        $oem3 = Oem::factory()->create([
            Oem::MANUAL_TYPE_IOM        => 'https://server.domain/file.pdf; http://server.domain/file.invalidpdf;',
            Oem::MANUAL_TYPE_DIAGNOSTIC => 'http://other-server.domain/file.pdf',
            Oem::MANUAL_TYPE_GUIDELINES => 'http://other-server.domain/file.pdf',
        ]);

        $this->assertEquals(0, $oem0->manualsCount());
        $this->assertEquals(1, $oem1->manualsCount());
        $this->assertEquals(2, $oem2->manualsCount());
        $this->assertEquals(4, $oem3->manualsCount());
    }

    /**
     * @test
     * @dataProvider dataProvider
     */
    public function it_returns_a_collection_of_manuals_filtered_by_manual_type(?string $rawManuals, int $expectedCount)
    {
        $oem = Oem::factory()->create([
            Oem::MANUAL_TYPE_IOM => $rawManuals,
        ]);

        $manuals = $oem->manualType(Oem::MANUAL_TYPE_IOM);

        $this->assertCount($expectedCount, $manuals);
        $manuals->each(function($item) use ($rawManuals) {
            $expectedManuals = Collection::make(explode(';', $rawManuals))->transform(function($item) {
                return trim($item);
            })->filter();
            $this->assertTrue($expectedManuals->contains($item));
        });
    }

    public function dataProvider(): array
    {
        return [
            [null, 0],
            ['', 0],
            [' ', 0],
            ['manual-a', 1],
            [' manual-a', 1],
            ['manual-a ', 1],
            [' manual-a ', 1],
            ['manual-a;manual-b', 2],
            [';manual-a;manual-b', 2],
            ['manual-a;;manual-b', 2],
            ['manual-a;manual-b;', 2],
            ['manual-a; manual-b', 2],
            ['manual-a ;manual-b', 2],
            [' manual-a ;manual-b', 2],
            ['manual-a; manual-b ', 2],
            ['; ;', 0],
            [';', 0],
        ];
    }

    /** @test */
    public function it_returns_a_collection_of_all_its_manuals()
    {
        $oem = Oem::factory()->create($data = [
            Oem::MANUAL_TYPE_GUIDELINES       => 'manual-a',
            Oem::MANUAL_TYPE_DIAGNOSTIC       => 'manual-b;manual-c',
            Oem::MANUAL_TYPE_IOM              => null,
            Oem::MANUAL_TYPE_MISCELLANEOUS    => 'manual-d',
            Oem::MANUAL_TYPE_PRODUCT_DATA     => 'manual-e;manual-f',
            Oem::MANUAL_TYPE_SERVICE_FACTS    => null,
            Oem::MANUAL_TYPE_WIRING_DIAGRAM   => ';manual-g',
            Oem::MANUAL_TYPE_CONTROLS_MANUALS => 'manual-h;',
        ]);

        $manuals    = $oem->manuals();
        $allManuals = Collection::make($data)->map(function($item) {
            return Collection::make(explode(';', $item))->filter();
        });

        $this->assertCount(8, $manuals);
        $manuals->each(function($item, $index) use ($allManuals) {
            $this->assertEquals($item, $allManuals[$index]);
        });
    }

    /** @test */
    public function its_post_count_is_0_if_it_has_no_series()
    {
        $oem = new Oem();
        $this->assertSame(0, $oem->postsCount());
    }

    /** @test */
    public function it_knows_its_posts_count()
    {
        $oem       = Oem::factory()->create();
        $modelType = $oem->modelType;
        $series    = $oem->series;

        Tag::factory()->count(2)->create();
        Tag::factory()->usingSeries($series)->create();
        Tag::factory()->usingSeries($series)->usingModelType($modelType)->create();
        Tag::factory()->usingModelType($modelType)->create();

        $this->assertSame(3, $oem->postsCount());
    }
}
