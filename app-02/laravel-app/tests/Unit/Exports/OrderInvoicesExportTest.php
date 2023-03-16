<?php

namespace Tests\Unit\Exports;

use App\Exports\OrderInvoicesExport;
use App\Models\Order;
use App\Models\OrderInvoice;
use App\Models\OrderLockedData;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use ReflectionClass;
use ReflectionException;
use Tests\TestCase;

class OrderInvoicesExportTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_implements_interfaces()
    {
        $reflection = new ReflectionClass(OrderInvoicesExport::class);

        $this->assertTrue($reflection->implementsInterface(WithMapping::class));
        $this->assertTrue($reflection->implementsInterface(WithHeadings::class));
        $this->assertTrue($reflection->implementsInterface(FromQuery::class));
    }

    /** @test
     * @throws ReflectionException
     */
    public function it_uses_exportable_trait()
    {
        $this->assertUseTrait(OrderInvoicesExport::class, Exportable::class);
    }

    /** @test */
    public function it_returns_a_query()
    {
        Carbon::setTestNow('2022-06-08 00:00:00');

        $fromDate = Carbon::now()->subMonth()->startOfMonth()->toDateTimeString();
        $tillDate = Carbon::now()->subMonth()->endOfMonth()->toDateTimeString();

        $expected = OrderInvoice::whereBetween('created_at', [$fromDate, $tillDate])->whereNull('processed_at');

        $result = (new OrderInvoicesExport())->query();

        $this->assertInstanceOf(Builder::class, $result);
        $this->assertSame($expected->toSql(), $result->toSql());
    }

    /** @test
     * @dataProvider datesDataProvider
     */
    public function it_includes_data_from_start_and_end_of_month(string $date)
    {
        Carbon::setTestNow('2022-06-08 00:00:00');

        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->create();
        OrderInvoice::factory()->usingOrder($order)->create(['created_at' => $date]);
        OrderInvoice::factory()->sequence(function() use ($supplier) {
            return ['order_id' => Order::factory()->usingSupplier($supplier)->create()];
        })->count(3)->create();

        $this->assertCount(1, (new OrderInvoicesExport())->query()->get());
    }

    public function datesDataProvider(): array
    {
        return [
            ['2022-05-01 00:00:00'],
            ['2022-05-31 23:59:59'],
            ['2022-05-10 00:00:00'],
        ];
    }

    /** @test */
    public function it_returns_headings()
    {
        $expected = [
            'number',
            'customer_id',
            'customer',
            'date',
            'due_date',
            'payment_terms',
            'item',
            'quantity',
            'unit_cost',
            'line_item_metadata.date',
            'line_item_metadata.bid_number',
            'line_item_metadata.contractor_name',
            'line_item_metadata.po_number',
            'line_item_metadata.order_total',
            'line_item_metadata.transaction_fee',
            'branch',
        ];

        $this->assertSame($expected, (new OrderInvoicesExport())->headings());
    }

    /** @test
     * @dataProvider typesDataProvider
     */
    public function it_returns_values(string $type)
    {
        $supplier      = Supplier::factory()->createQuietly();
        $order         = Order::factory()->usingSupplier($supplier)->create();
        $orderInvoice  = OrderInvoice::factory()->usingOrder($order)->create(['type' => $type]);
        $subtotal      = (string) ($type === 'credit' ? "($orderInvoice->subtotal)" : $orderInvoice->subtotal);
        $user          = $order->user;
        $middleOfMonth = Carbon::now()->setDay(15)->format('M-d-Y');

        $expected = [
            'BL-' . $orderInvoice->number,
            $supplier->id,
            $supplier->name,
            $middleOfMonth,
            $middleOfMonth,
            $orderInvoice->payment_terms,
            'BluonLive',
            $subtotal,
            (string) ($orderInvoice->take_rate / 10000),
            $orderInvoice->created_at->format('M-d-Y'),
            (string) $orderInvoice->bid_number,
            $user->companyName(),
            $orderInvoice->order_name,
            $subtotal,
            $orderInvoice->take_rate / 100 . '%',
            $supplier->branch,
        ];

        $this->assertSame($expected, (new OrderInvoicesExport())->map($orderInvoice));
    }

    public function typesDataProvider(): array
    {
        return [
            ['credit'],
            ['invoice'],
        ];
    }

    /** @test */
    public function it_returns_values_with_a_deleted_user()
    {
        $supplier     = Supplier::factory()->createQuietly();
        $order        = Order::factory()->usingSupplier($supplier)->create(['user_id' => null]);
        $orderInvoice = OrderInvoice::factory()->invoice()->usingOrder($order)->create();
        $subtotal     = (string) $orderInvoice->subtotal;
        OrderLockedData::factory()->usingOrder($order)->create(['user_company' => $userCompany = 'Company']);
        $middleOfMonth = Carbon::now()->setDay(15)->format('M-d-Y');

        $expected = [
            'BL-' . $orderInvoice->number,
            $supplier->id,
            $supplier->name,
            $middleOfMonth,
            $middleOfMonth,
            $orderInvoice->payment_terms,
            'BluonLive',
            $subtotal,
            (string) ($orderInvoice->take_rate / 10000),
            $orderInvoice->created_at->format('M-d-Y'),
            (string) $orderInvoice->bid_number,
            $userCompany,
            $orderInvoice->order_name,
            $subtotal,
            $orderInvoice->take_rate / 100 . '%',
            $supplier->branch,
        ];

        $this->assertSame($expected, (new OrderInvoicesExport())->map($orderInvoice));
    }
}
