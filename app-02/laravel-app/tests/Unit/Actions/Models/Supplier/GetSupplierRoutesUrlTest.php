<?php

namespace Tests\Unit\Actions\Models\Supplier;

use App\Actions\Models\Supplier\GetSupplierRoutesUrl;
use App\Models\Order;
use App\Models\Supplier;
use App\Models\User;
use Config;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetSupplierRoutesUrlTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_gets_inbound_url_if_user_has_unprocessed_orders()
    {
        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        Order::factory()->usingUser($user)->usingSupplier($supplier)->pending()->create();
        Order::factory()->usingUser($user)->usingSupplier($supplier)->pendingApproval()->create();

        $inboundUrl = Config::get('live.url') . Config::get('live.routes.inbound');

        $this->assertSame($inboundUrl, (new GetSupplierRoutesUrl($supplier, $user))->execute());
    }

    /** @test */
    public function it_gets_inbound_url_if_user_has_not_orders()
    {
        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();

        $inboundUrl = Config::get('live.url') . Config::get('live.routes.inbound');

        $this->assertSame($inboundUrl, (new GetSupplierRoutesUrl($supplier, $user))->execute());
    }

    /** @test */
    public function it_gets_outbound_url_if_user_has_order_status_different_from_unprocessed()
    {
        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        Order::factory()->usingUser($user)->usingSupplier($supplier)->approved()->create();
        Order::factory()->usingUser($user)->usingSupplier($supplier)->completed()->create();

        $outboundUrl = Config::get('live.url') . Config::get('live.routes.outbound');

        $this->assertSame($outboundUrl, (new GetSupplierRoutesUrl($supplier, $user))->execute());
    }
}
