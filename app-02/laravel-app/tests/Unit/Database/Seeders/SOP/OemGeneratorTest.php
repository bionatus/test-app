<?php

namespace Tests\Unit\Database\Seeders\SOP;

use App\Models\Series;
use Database\Seeders\SOP\OemGenerator;
use Exception;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class OemGeneratorTest extends TestCase
{
    use RefreshDatabase;

    private array      $seriesNames;
    private Collection $oemUuids;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seriesNames = [
            'BP',
            'D*CG',
            'B*HZ',
            'TWX0',
            'Voyager',
            'TWP0',
            'RLMB',
            'RJMA',
            'RJNA',
            'RRKA-A',
            'RKNB',
            '38BRG',
            '48TC',
            '48SD',
            '48HJE',
            'R4GE',
        ];
        $this->oemUuids    = Collection::make([
            '03BB4819-7F61-41BA-9E11-60BA19543C42',
            '0C8FE34C-6783-4924-9369-86925F9581DB',
            '11835364-55FE-4BBF-99B0-BA653D6DCCBE',
            '1AF24BF4-409D-4361-B5D7-4781B8F6A6E7',
            'C8C742DB-571F-43C0-99D0-45ACED515807',
            '264266BB-7A2F-4C56-9829-CF64CE0CE986',
            '6766279C-E211-4203-B8A8-5E1E698B5ED1',
            '43924EDC-DF4B-4F5B-A2F8-5B2807FEB8E8',
            '4A60568E-06E2-4967-8B82-C52D365097C3',
            '4EFF39EA-6B79-4C76-8D80-2C8F345A503D',
            '5F2E4E93-1F29-4F83-B3E4-6C46C16AB085',
            '756BA061-7E3F-49CE-959E-66EEEA51FCE2',
            '51096C38-2AF8-4F4C-89C6-7E7F5BD97984',
            '717AA821-0420-4CAB-861C-1B73F9733709',
            '8C1881B9-4495-4D58-ABB9-DAA0BB13530C',
            'B2963F2B-E1A5-4B76-8E28-3C1638973EEE',
            'CC5189EC-A24E-4566-A121-C2E4EF418CA2',
            'EBD00DF9-27E0-4926-ADA4-802A0793A796',
            'F3CC58C2-FEEA-4126-9BAA-71D739868453',
            'F3CC58C2-FEEA-4126-9BAA-71D739868464',
        ]);
    }

    /** @test
     * @throws Exception
     */
    public function it_generates_oems_for_specific_series()
    {
        $series    = Series::factory()->count(16)->sequence(fn(Sequence $sequence
        ) => ['name' => $this->seriesNames[$sequence->index]])->create();
        $oemHelper = new OemGenerator($series);
        $oemHelper->createOems();

        $this->assertDatabaseCount('oems', count(OemGenerator::OEMS));
        $this->oemUuids->each(function($uuid) {
            $this->assertDatabaseHas('oems', ['uuid' => $uuid]);
        });
    }

    /** @test
     * @throws Exception
     */
    public function it_returns_a_list_of_created_oems()
    {
        $series    = Series::factory()->count(16)->sequence(fn(Sequence $sequence
        ) => ['name' => $this->seriesNames[$sequence->index]])->create();
        $oemHelper = new OemGenerator($series);
        $oemHelper->createOems();

        $this->assertEquals($this->oemUuids, $oemHelper->getOems()->pluck('uuid'));
    }
}
