<?php

namespace Tests\Unit\Console\Commands;

use App\Console\Commands\CopyFilesWithAirtableUrlToAssetsDiskCommand;
use App\Jobs\CopyFilesFromUrlToAssetsDisk;
use App\Models\Brand;
use App\Models\Oem;
use App\Models\Part;
use App\Models\Series;
use Bus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** @see CopyFilesWithAirtableUrlToAssetsDiskCommand */
class CopyFilesWithAirtableUrlToAssetsDiskCommandTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var string[]
     */
    private array $answers;

    protected function setUp(): void
    {
        parent::setUp();

        $this->answers = [Brand::class, Oem::class, Part::class, Series::class];
    }

    /** @test
     * @dataProvider dataProvider
     */
    public function it_dispatch_the_job_when_there_are_model_instances_with_airtable_files($answer, $times)
    {
        Bus::fake([CopyFilesFromUrlToAssetsDisk::class]);
        Brand::factory()->create([
            'logo' => [
                'url'        => 'https://dl.airtable.com/1oDE0aq3BQ4KGBEJZHgmi_goodman.png',
                'thumbnails' => [
                    'full'  => ['url' => 'https://dl.airtable.com/2oDE0aq3BQ4KGBEJZHgmi_goodman.png'],
                    'large' => ['url' => 'https://dl.airtable.com/3oDE0aq3BQ4KGBEJZHgmi_goodman.png'],
                    'small' => ['url' => 'https://dl.airtable.com/3oDE0aq3BQ4KGBEJZHgmi_goodman.png'],
                ],
            ],
        ]);

        Part::factory()->create(['image' => 'image airtable']);
        Oem::factory()->create([
            'bluon_guidelines' => 'bluon_guidelines airtable',
            'controls_manuals' => 'controls_manuals airtable',
            'diagnostic'       => 'diagnostic airtable',
            'iom'              => 'iom airtable',
            'logo'             => 'logo airtable',
            'misc'             => 'misc airtable',
            'product_data'     => 'product_data airtable',
            'service_facts'    => 'service_facts airtable',
            'unit_image'       => 'unit_image airtable',
            'wiring_diagram'   => 'wiring_diagram airtable',
        ]);
        Series::factory()
            ->create(['image' => 'https://dl.airtable.com/.attachments/cb3c686e9c7b596f8267f4eb5cca88ae/62b423e3/48HJ-32SI-1IOMERROR-FLASHCODES.pdf;https://dl.airtable.com/.attachments/95c5858988b8d656ea4ef1966a57790b/125abf5b/48HJ-32SI-1IOMTROUBLESHOOTING.pdf']);

        $this->artisan('copy-airtable-files-to-assets-disk')
            ->expectsChoice('Choose a model', $answer, $this->answers)
            ->assertSuccessful();

        Bus::assertDispatched(CopyFilesFromUrlToAssetsDisk::class, $times);
    }

    /** @test
     * @dataProvider dataProvider
     */
    public function it_does_not_dispatch_the_job_when_there_are_no_model_instances_with_airtable_files($answer)
    {
        Bus::fake([CopyFilesFromUrlToAssetsDisk::class]);
        Brand::factory()->create([
            'logo' => [
                'url'        => 'https://dl.yyy.com/1oDE0aq3BQ4KGBEJZHgmi_goodman.png',
                'thumbnails' => [
                    'full'  => ['url' => 'https://dl.yyy.com/2oDE0aq3BQ4KGBEJZHgmi_goodman.png'],
                    'large' => ['url' => 'https://dl.yyy.com/3oDE0aq3BQ4KGBEJZHgmi_goodman.png'],
                    'small' => ['url' => 'https://dl.yyy.com/3oDE0aq3BQ4KGBEJZHgmi_goodman.png'],
                ],
            ],
        ]);
        Part::factory()->create(['image' => 'image']);
        Oem::factory()->create([
            'bluon_guidelines' => 'bluon_guidelines',
            'controls_manuals' => 'controls_manuals',
            'diagnostic'       => 'diagnostic',
            'iom'              => 'iom',
            'logo'             => 'logo',
            'misc'             => 'misc',
            'product_data'     => 'product_data',
            'service_facts'    => 'service_facts',
            'unit_image'       => 'unit_image',
            'wiring_diagram'   => 'wiring_diagram',
        ]);

        Series::factory()
            ->create(['image' => 'https://dl.yyy.com/.attachments/cb3c686e9c7b596f8267f4eb5cca88ae/62b423e3/48HJ-32SI-1IOMERROR-FLASHCODES.pdf;https://dl.yyy.com/.attachments/95c5858988b8d656ea4ef1966a57790b/125abf5b/48HJ-32SI-1IOMTROUBLESHOOTING.pdf']);

        $this->artisan('copy-airtable-files-to-assets-disk')
            ->expectsChoice('Choose a model', $answer, $this->answers)
            ->assertSuccessful();

        Bus::assertNotDispatched(CopyFilesFromUrlToAssetsDisk::class);
    }

    public function dataProvider(): array
    {
        return [
            // model class, callbacks
            [Oem::class, 10],
            [Part::class, 1],
            [Brand::class, 3],
            [Series::class, 2],
        ];
    }

    /** @test */
    public function it_dispatches_the_job_just_one_time_when_there_are_several_instances_with_the_same_url()
    {
        Bus::fake([CopyFilesFromUrlToAssetsDisk::class]);

        Part::factory()->create(['image' => 'image airtable']);
        Part::factory()->create(['image' => 'image airtable']);
        Part::factory()->create(['image' => 'other image;image airtable']);

        $this->artisan('copy-airtable-files-to-assets-disk')
            ->expectsChoice('Choose a model', Part::class, $this->answers)
            ->assertSuccessful();

        Bus::assertDispatched(CopyFilesFromUrlToAssetsDisk::class, 1);
    }

    /** @test */
    public function it_dispatches_multiple_jobs_when_the_field_value_has_multiple_urls()
    {
        Bus::fake([CopyFilesFromUrlToAssetsDisk::class]);

        Part::factory()->create(['image' => 'image airtable;image2 airtable;image3 airtable;image4']);

        $this->artisan('copy-airtable-files-to-assets-disk')
            ->expectsChoice('Choose a model', Part::class, $this->answers)
            ->assertSuccessful();

        Bus::assertDispatched(CopyFilesFromUrlToAssetsDisk::class, 3);
    }

    /** @test */
    public function it_dispatches_jobs_when_there_are_brand_instances_with_airtable_files_and_a_different_logo_structure(
    )
    {
        Bus::fake([CopyFilesFromUrlToAssetsDisk::class]);
        Brand::factory()->create([
            'logo' => [
                "id"         => "attob2YUS5Rb19ykB",
                "url"        => "https://dl.airtable.com/L61scszCQbO8gLZNK1yD_allied.png",
                "size"       => 4821,
                "type"       => "image/png",
                "width"      => 120,
                "height"     => 60,
                "filename"   => "allied.png",
                "thumbnails" => [
                    "full"  => [
                        "url"    => "https://dl.airtable.com/C3jQ8fBS5GoQ2xtbt5Y8_full_allied.png",
                        "width"  => 120,
                        "height" => 60,
                    ],
                    "large" => [
                        "url"    => "https://dl.airtable.com/cSenx1cfROubXqEjKJwT_large_allied.png",
                        "width"  => 120,
                        "height" => 60,
                    ],
                    "small" => [
                        "url"    => "https://dl.airtable.com/E9cDBxcXTpyFnt6RtMfB_small_allied.png",
                        "width"  => 72,
                        "height" => 36,
                    ],
                ],
            ],
        ]);

        $this->artisan('copy-airtable-files-to-assets-disk')
            ->expectsChoice('Choose a model', Brand::class, $this->answers)
            ->assertSuccessful();

        Bus::assertDispatched(CopyFilesFromUrlToAssetsDisk::class, 4);
    }
}
