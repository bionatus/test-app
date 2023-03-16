<?php

namespace Tests\Unit\Http\Resources\Api\V3\Account\Point\Redemption;

use App\Http\Resources\Api\V3\Account\Point\Redemption\BaseResource;
use App\Models\XoxoRedemption;
use Carbon\Carbon;
use Mockery;
use Tests\TestCase;

class BaseResourceTest extends TestCase
{
    /** @test */
    public function it_has_correct_fields()
    {
        $redemption = Mockery::mock(XoxoRedemption::class);
        $redemption->shouldReceive('getAttribute')->with('redemption_code')->once()->andReturn($redemptionCode = 123);
        $redemption->shouldReceive('getAttribute')->with('voucher_code')->once()->andReturn($voucherCode = 321);
        $redemption->shouldReceive('getAttribute')->with('name')->once()->andReturn($name = 'Test name voucher');
        $redemption->shouldReceive('getAttribute')->with('image')->once()->andReturn($image = 'Image Url');
        $redemption->shouldReceive('getAttribute')
            ->with('value_denomination')
            ->once()
            ->andReturn($valueDenomination = 1000);
        $redemption->shouldReceive('getAttribute')->with('amount_charged')->once()->andReturn($amountCharged = 2000);
        $redemption->shouldReceive('getAttribute')
            ->with('description')
            ->once()
            ->andReturn($description = 'Lorem Description');
        $redemption->shouldReceive('getAttribute')
            ->with('instructions')
            ->once()
            ->andReturn($instructions = 'Lorem Instructions');
        $redemption->shouldReceive('getAttribute')
            ->with('terms_conditions')
            ->once()
            ->andReturn($termsAndConditions = 'Lorem Terms and Conditions');
        $redemption->shouldReceive('getAttribute')->with('created_at')->once()->andReturn($createdAt = Carbon::now());
        $redemption->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = 'uuid');

        $resource = new BaseResource($redemption);

        $response = $resource->resolve();

        $data = [
            'id'                   => $id,
            'redemption_code'      => $redemptionCode,
            'voucher_code'         => $voucherCode,
            'name'                 => $name,
            'image'                => $image,
            'value_denomination'   => $valueDenomination,
            'amount_charged'       => $amountCharged,
            'description'          => $description,
            'instructions'         => $instructions,
            'terms_and_conditions' => $termsAndConditions,
            'created_at'           => $createdAt,
        ];

        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
        $this->assertEquals($data, $response);
    }
}
