<?php

namespace Tests\Unit\Database\Seeders;

use App\Constants\Environments;
use App\Constants\Filesystem;
use App\Models\User;
use Database\Seeders\DevelopmentUsersSeeder;
use Database\Seeders\EnvironmentSeeder;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionClass;
use Storage;
use Tests\TestCase;

class DevelopmentUsersSeederTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_implements_interface()
    {
        $reflection = new ReflectionClass(DevelopmentUsersSeeder::class);

        $this->assertTrue($reflection->implementsInterface(EnvironmentSeeder::class));
    }

    const EMAILS = [
        'gabriel.zanetti@devbase.us',
        'matias.velilla@devbase.us',
        'emilio.bottino@devbase.us',
        'joaquin.steffan@devbase.us',
        'jorge.moreno@devbase.us',
        // BE
        'alejandro.rohmer@devbase.us',
        'carlos.rojas@devbase.us',
        'robert.lopez@devbase.us',
        'samir.fragozo@devbase.us',
        'fernando.ortuno@devbase.us',
        'facundo.condal@devbase.us',
        'damian.dicostanzo@devbase.us',
        'diego.romero@devbase.us',
        // FE
        'fernando.keim@devbase.us',
        'josue.angarita@devbase.us',
        'matias.nasiff@devbase.us',
        'roni.castro@devbase.us',
        'thiago.corta@devbase.us',
        'marcos.godoy@devbase.us',
        'alexis.borges@devbase.us',
        'reubert.barbosa@devbase.us',
        'dev@example.com',
        // QA
        'constanza.giorgetti@devbase.us',
        'emilia.bejarano@devbase.us',
        'kevin.di.julio@devbase.us',
        'walter.nolasco@devbase.us',
        'lucas.vazquez@devbase.us',
        'carlos.viniales@devbase.us',
        'estanislao.larriera@devbase.us',
        'tomas.ricolfi@devbase.us',
        'qabluon@gmail.com',
    ];

    /** @test */
    public function it_uses_specific_emails()
    {
        $this->assertEqualsCanonicalizing(self::EMAILS, DevelopmentUsersSeeder::EMAILS);
    }

    /** @test
     * @throws FileNotFoundException
     */
    public function it_stores_development_users()
    {
        Storage::fake(Filesystem::DISK_DEVELOPMENT_MEDIA);
        Storage::fake('public');

        $seeder = new DevelopmentUsersSeeder();
        $seeder->run();

        foreach (self::EMAILS as $email) {
            $this->assertDatabaseHas(User::tableName(), [
                'email' => $email,
            ]);
        }
    }

    /** @test */
    public function it_runs_in_specific_environments()
    {
        $seeder   = new DevelopmentUsersSeeder();
        $expected = [
            Environments::LOCAL,
            Environments::DEVELOPMENT,
            Environments::QA,
            Environments::QA2,
            Environments::DEMO,
            Environments::STAGING,
            Environments::UAT,
        ];

        $this->assertEquals($expected, $seeder->environments());
    }
}
