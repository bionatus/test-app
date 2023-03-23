<?php

namespace Tests\Unit\Exports;

use App\Exports\OrderInvoicesSuppliersExport;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use ReflectionClass;
use Tests\CanRefreshDatabase;
use Tests\TestCase;

class OrderInvoicesSuppliersExportTest extends TestCase
{
    use CanRefreshDatabase;

    /** @test */
    public function it_implements_interfaces()
    {
        $reflection = new ReflectionClass(OrderInvoicesSuppliersExport::class);

        $this->assertTrue($reflection->implementsInterface(WithMapping::class));
        $this->assertTrue($reflection->implementsInterface(WithHeadings::class));
        $this->assertTrue($reflection->implementsInterface(FromQuery::class));
    }

    /** @test */
    public function it_uses_exportable_trait()
    {
        $this->assertUseTrait(OrderInvoicesSuppliersExport::class, Exportable::class);
    }

    /** @test */
    public function it_returns_a_query()
    {
        Carbon::setTestNow('2022-06-08 00:00:00');

        $month = Carbon::now()->subMonth()->month;

        $expected = Supplier::whereHas('orders.orderInvoices', function($query) use ($month) {
            $query->whereMonth('created_at', '=', $month);
        });

        $result = (new OrderInvoicesSuppliersExport())->query();

        $this->assertInstanceOf(Builder::class, $result);
        $this->assertSame($expected->toSql(), $result->toSql());
    }

    /** @test */
    public function it_returns_headings()
    {
        $expected = [
            'id',
            'email',
            'name',
            'address',
            'city',
            'state',
            'zip_code',
            'country',
            'phone',
            'notes',
        ];

        $this->assertSame($expected, (new OrderInvoicesSuppliersExport())->headings());
    }

    /** @test */
    public function it_return_values()
    {
        $this->refreshDatabaseForSingleTest();

        $supplier = Supplier::factory()->createQuietly();

        $expected = [
            $supplier->id,
            $supplier->email,
            $supplier->name,
            $supplier->address,
            $supplier->city,
            $supplier->state,
            $supplier->zip_code,
            $supplier->country,
            $supplier->phone,
            (string) ($supplier->take_rate / 100),
        ];

        $this->assertSame($expected, (new OrderInvoicesSuppliersExport())->map($supplier));
    }
}
