<?php

namespace Tests\Unit\Database\Seeders;

use App\Constants\Filesystem;
use App\Models\ForbiddenZipCode;
use Database\Seeders\EnvironmentSeeder;
use Database\Seeders\ForbiddenZipCodeSeeder;
use DB;
use ReflectionClass;
use Storage;
use Tests\CanRefreshDatabase;
use Tests\TestCase;

class ForbiddenZipCodeSeederTest extends TestCase
{
    use CanRefreshDatabase;

    /** @test */
    public function it_implements_interface()
    {
        $reflection = new ReflectionClass(ForbiddenZipCodeSeeder::class);

        $this->assertTrue($reflection->implementsInterface(EnvironmentSeeder::class));
    }

    /** @test
     * @throws \League\Flysystem\FileExistsException
     */
    public function it_stores_forbidden_zip_codes_located_in_a_csv_file()
    {
        if (DB::connection()->getName() == 'sqlite') {
            $this->refreshDatabaseForSingleTest();
        }

        Storage::fake(Filesystem::DISK_FILES);

        $forbiddenZipCodes = "522\n92274\n11111\n46988\n92277";

        Storage::disk(Filesystem::DISK_FILES)->write('forbidden_zip_codes.csv', $forbiddenZipCodes);

        (new ForbiddenZipCodeSeeder())->run();

        $this->assertDatabaseCount(ForbiddenZipCode::tableName(), 5);
        $this->assertDatabaseHas(ForbiddenZipCode::tableName(), ['zip_code' => '522']);
        $this->assertDatabaseHas(ForbiddenZipCode::tableName(), ['zip_code' => '92274']);
        $this->assertDatabaseHas(ForbiddenZipCode::tableName(), ['zip_code' => '11111']);
        $this->assertDatabaseHas(ForbiddenZipCode::tableName(), ['zip_code' => '46988']);
        $this->assertDatabaseHas(ForbiddenZipCode::tableName(), ['zip_code' => '92277']);

        DB::table(ForbiddenZipCode::tableName())->truncate();
    }

    /** @test
     * @throws \League\Flysystem\FileExistsException
     */
    public function it_truncates_table_before_create_the_data()
    {
        $this->shouldTruncateTable = true;
        if (DB::connection()->getName() == 'sqlite') {
            $this->refreshDatabaseForSingleTest();
        }

        Storage::fake(Filesystem::DISK_FILES);

        $forbiddenZipCodes = "522\n92274\n11111\n46988\n92277";

        Storage::disk(Filesystem::DISK_FILES)->write('forbidden_zip_codes.csv', $forbiddenZipCodes);

        (new ForbiddenZipCodeSeeder())->run();
        (new ForbiddenZipCodeSeeder())->run();

        $this->assertDatabaseCount(ForbiddenZipCode::tableName(), 5);
        $this->assertDatabaseHas(ForbiddenZipCode::tableName(), ['zip_code' => '522']);
        $this->assertDatabaseHas(ForbiddenZipCode::tableName(), ['zip_code' => '92274']);
        $this->assertDatabaseHas(ForbiddenZipCode::tableName(), ['zip_code' => '11111']);
        $this->assertDatabaseHas(ForbiddenZipCode::tableName(), ['zip_code' => '46988']);
        $this->assertDatabaseHas(ForbiddenZipCode::tableName(), ['zip_code' => '92277']);

        DB::table(ForbiddenZipCode::tableName())->truncate();
    }
}
