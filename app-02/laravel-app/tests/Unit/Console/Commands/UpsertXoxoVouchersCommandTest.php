<?php

namespace Tests\Unit\Console\Commands;

use App;
use App\Models\Scopes\Published;
use App\Models\XoxoVoucher;
use App\Services\Xoxo\Xoxo;
use App\Types\XoxoVoucher as XoxoVoucherType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Mockery;
use Tests\TestCase;

class UpsertXoxoVouchersCommandTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_creates_a_new_xoxo_voucher()
    {
        $item = [
            'productId'                      => $code = 1,
            'name'                           => $name = 'fake name 1',
            'imageUrl'                       => $imageUrl = 'fake image url 1',
            'valueDenominations'             => $valueDenominations = 'fake value denomination 1',
            'description'                    => $description = 'fake description',
            'redemptionInstructions'         => $instructions = 'fake instructions',
            'termsAndConditionsInstructions' => $terms = 'fake terms and conditions',
        ];

        $firstVoucher = App::make(XoxoVoucherType::class, ['item' => $item]);
        $vouchers     = Collection::make([$firstVoucher]);

        $xoxo = Mockery::mock(Xoxo::class);
        $xoxo->shouldReceive('getRedeemMethods')->withNoArgs()->once()->andReturn($vouchers);
        App::bind(Xoxo::class, fn() => $xoxo);

        $this->artisan('xoxo:upsert-vouchers')->assertSuccessful();

        $this->assertDatabaseHas(XoxoVoucher::tableName(), [
            'code'                => $code,
            'name'                => $name,
            'image'               => $imageUrl,
            'value_denominations' => $valueDenominations,
            'description'         => $description,
            'instructions'        => $instructions,
            'terms_conditions'    => $terms,
            'published_at'        => Carbon::now(),
        ]);
    }

    /** @test */
    public function it_unpublishes_xoxo_vouchers_not_returned_in_xoxo_service()
    {
        XoxoVoucher::factory()->published()->count(3)->create();
        $voucher = XoxoVoucher::factory()->create();

        $item = [
            'productId'                      => $voucher->code,
            'name'                           => 'fake name 1',
            'imageUrl'                       => 'fake image url 1',
            'valueDenominations'             => 'fake value denomination 1',
            'description'                    => 'fake description',
            'redemptionInstructions'         => 'fake instructions',
            'termsAndConditionsInstructions' => 'fake terms and conditions',
        ];

        $firstVoucher = App::make(XoxoVoucherType::class, ['item' => $item]);
        $voucher      = Collection::make([$firstVoucher]);

        $xoxo = Mockery::mock(Xoxo::class);
        $xoxo->shouldReceive('getRedeemMethods')->withNoArgs()->once()->andReturn($voucher);
        App::bind(Xoxo::class, fn() => $xoxo);

        $this->artisan('xoxo:upsert-vouchers')->assertSuccessful();

        $this->assertDatabaseCount(XoxoVoucher::tableName(), 4);

        $xoxoVouchersUnpublished = XoxoVoucher::whereNull('published_at')->count();
        $this->assertEquals(3, $xoxoVouchersUnpublished);

        $xoxoVouchersPublished = XoxoVoucher::scoped(new Published())->count();
        $this->assertEquals(1, $xoxoVouchersPublished);
    }

    /** @test */
    public function it_publishes_xoxo_vouchers_returned_in_xoxo_service()
    {
        Carbon::setTestNow('2021-02-13 12:20:00');

        $voucher = XoxoVoucher::factory()->unpublished()->create();

        $item = [
            'productId'                      => $code = $voucher->code,
            'name'                           => $name = 'fake name 1',
            'imageUrl'                       => $imageUrl = 'fake image url 1',
            'valueDenominations'             => $valueDenominations = 'fake value denomination 1',
            'description'                    => $description = 'fake description',
            'redemptionInstructions'         => $instructions = 'fake instructions',
            'termsAndConditionsInstructions' => $terms = 'fake terms and conditions',
        ];

        $firstVoucher = App::make(XoxoVoucherType::class, ['item' => $item]);
        $voucher      = Collection::make([$firstVoucher]);

        $xoxo = Mockery::mock(Xoxo::class);
        $xoxo->shouldReceive('getRedeemMethods')->withNoArgs()->once()->andReturn($voucher);
        App::bind(Xoxo::class, fn() => $xoxo);

        $this->artisan('xoxo:upsert-vouchers')->assertSuccessful();

        $this->assertDatabaseCount(XoxoVoucher::tableName(), 1);

        $xoxoVouchersPublished = XoxoVoucher::scoped(new Published())->count();
        $this->assertEquals($xoxoVouchersPublished, 1);

        $this->assertDatabaseHas(XoxoVoucher::tableName(), [
            'code'                => $code,
            'name'                => $name,
            'image'               => $imageUrl,
            'value_denominations' => $valueDenominations,
            'description'         => $description,
            'instructions'        => $instructions,
            'terms_conditions'    => $terms,
            'published_at'        => Carbon::now(),
        ]);
    }

    /** @test */
    public function it_updates_xoxo_vouchers_returned_in_xoxo_service()
    {
        Carbon::setTestNow('2021-02-13 12:20:00');

        XoxoVoucher::factory()->published()->create([
            'code'                => 1,
            'name'                => 'initial name',
            'image'               => 'initial imagen',
            'value_denominations' => 'initial denomination',
            'description'         => 'initial description',
            'instructions'        => 'initial instructions',
            'terms_conditions'    => 'initial terms_conditions',
            'published_at'        => Carbon::now()->subDay(),
        ]);

        $item = [
            'productId'                      => $code = 1,
            'name'                           => $name = 'final name',
            'imageUrl'                       => $imageUrl = 'final imagen',
            'valueDenominations'             => $valueDenominations = 'final value denomination',
            'description'                    => $description = 'final description',
            'redemptionInstructions'         => $instructions = 'final instructions',
            'termsAndConditionsInstructions' => $terms = 'final terms and conditions',
        ];

        $firstVoucher = App::make(XoxoVoucherType::class, ['item' => $item]);
        $voucher      = Collection::make([$firstVoucher]);

        $xoxo = Mockery::mock(Xoxo::class);
        $xoxo->shouldReceive('getRedeemMethods')->withNoArgs()->once()->andReturn($voucher);
        App::bind(Xoxo::class, fn() => $xoxo);

        $this->artisan('xoxo:upsert-vouchers')->assertSuccessful();

        $this->assertDatabaseCount(XoxoVoucher::tableName(), 1);

        $xoxoVouchersPublished = XoxoVoucher::scoped(new Published())->count();
        $this->assertEquals(1, $xoxoVouchersPublished);

        $this->assertDatabaseHas(XoxoVoucher::tableName(), [
            'code'                => $code,
            'name'                => $name,
            'image'               => $imageUrl,
            'value_denominations' => $valueDenominations,
            'description'         => $description,
            'instructions'        => $instructions,
            'terms_conditions'    => $terms,
            'published_at'        => Carbon::now(),
        ]);
    }

    /** @test */
    public function it_sorts_value_denominations()
    {
        $item = [
            'productId'                      => $code = 1,
            'name'                           => $name = 'fake name 1',
            'imageUrl'                       => $imageUrl = 'fake image url 1',
            'valueDenominations'             => '100,1000,1,15,10,2,25,50,3',
            'description'                    => $description = 'fake description',
            'redemptionInstructions'         => $instructions = 'fake instructions',
            'termsAndConditionsInstructions' => $terms = 'fake terms and conditions',
        ];

        $firstVoucher = App::make(XoxoVoucherType::class, ['item' => $item]);
        $vouchers     = Collection::make([$firstVoucher]);

        $xoxo = Mockery::mock(Xoxo::class);
        $xoxo->shouldReceive('getRedeemMethods')->withNoArgs()->once()->andReturn($vouchers);
        App::bind(Xoxo::class, fn() => $xoxo);

        $this->artisan('xoxo:upsert-vouchers')->assertSuccessful();

        $this->assertDatabaseHas(XoxoVoucher::tableName(), [
            'code'                => $code,
            'name'                => $name,
            'image'               => $imageUrl,
            'value_denominations' => '1,2,3,10,15,25,50,100,1000',
            'description'         => $description,
            'instructions'        => $instructions,
            'terms_conditions'    => $terms,
            'published_at'        => Carbon::now(),
        ]);
    }
}
