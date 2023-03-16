<?php

namespace Tests\Unit\Database\Seeders;

use App\Constants\Environments;
use App\Models\Term;
use Database\Seeders\EnvironmentSeeder;
use Database\Seeders\TermsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionClass;
use Tests\TestCase;

class TermsSeederTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_implements_interface()
    {
        $reflection = new ReflectionClass(TermsSeeder::class);

        $this->assertTrue($reflection->implementsInterface(EnvironmentSeeder::class));
    }

    const TERMS = [
        [
            'title'       => 'Initial Term',
            'body'        => 'Initial Body term',
            'link'        => 'https://www.bluon.com/',
            'required_at' => '2022-09-01 00:00:00',
        ],
    ];

    /** @test
     */
    public function it_stores_development_users()
    {
        $seeder = new TermsSeeder();
        $seeder->run();

        foreach (self::TERMS as $term) {
            $this->assertDatabaseHas(TERM::tableName(), $term);
        }
    }

    /** @test */
    public function it_runs_in_specific_environments()
    {
        $seeder   = new TermsSeeder();
        $expected = [
            Environments::LOCAL,
            Environments::DEVELOPMENT,
            Environments::QA,
            Environments::QA2,
            Environments::DEMO,
            Environments::STAGING,
            Environments::UAT,
            Environments::PRODUCTION,
        ];

        $this->assertEquals($expected, $seeder->environments());
    }
}
