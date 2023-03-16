<?php

namespace Tests\Unit\Models;

use App\Models\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Schema;
use Tests\TestCase;

abstract class ModelTestCase extends TestCase
{
    use RefreshDatabase;

    protected Model $instance;

    /** @test */
    public function it_returns_table_name()
    {
        $concrete = new class extends Model {
            protected $table = 'table';
        };

        $this->assertEquals('table', $concrete->tableName());
    }

    public function assertHasExpectedColumns(string $tableName, array $expectedColumns): void
    {
        $missing = array_diff($expectedColumns, Schema::getColumnListing($tableName));

        $this->assertTrue(Schema::hasColumns($tableName, $expectedColumns),
            "Columns missing in table {$tableName}: " . implode(', ', $missing));

        $diff = array_diff(Schema::getColumnListing($tableName), $expectedColumns);

        $this->assertCount(0, $diff, "Columns mismatch in table {$tableName}: " . implode(', ', $diff));
    }
}
