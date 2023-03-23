<?php

namespace Tests\Unit\Http\Requests\LiveApi\V1\Setting;

use App\Constants\RequestKeys;
use App\Http\Requests\LiveApi\V1\Setting\IndexRequest;
use Illuminate\Support\Str;
use Lang;
use Tests\CanRefreshDatabase;
use Tests\Unit\Http\Requests\RequestTestCase;

class IndexRequestTest extends RequestTestCase
{
    use CanRefreshDatabase;

    protected string $requestClass = IndexRequest::class;

    /** @test */
    public function it_may_not_get_a_param()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationPassed();
    }

    /** @test */
    public function its_group_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::GROUP => ['array item']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::GROUP]);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => RequestKeys::GROUP])]);
    }

    /** @test */
    public function its_group_parameter_must_be_a_string_of_no_more_than_255_characters()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::GROUP => Str::random(256)]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::GROUP]);
        $request->assertValidationMessages([
            Lang::get('validation.max.string', [
                'attribute' => RequestKeys::GROUP,
                'max'       => 255,
            ]),
        ]);
    }

    /** @test
     * @dataProvider provider
     */
    public function it_pass_on_valid_data(?string $value)
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::GROUP => $value,
        ]);

        $request->assertValidationPassed();
    }

    public function provider(): array
    {
        return [
            [null],
            ['foo'],
        ];
    }
}
