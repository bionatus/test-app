<?php

namespace Tests\Unit\Http\Requests\LiveApi\V2\Order\Invoice;

use App\Constants\RequestKeys;
use App\Http\Controllers\LiveApi\V2\Order\InvoiceController;
use App\Http\Requests\LiveApi\V2\Order\Invoice\StoreRequest;
use Illuminate\Http\UploadedFile;
use Lang;
use Tests\CanRefreshDatabase;
use Tests\Unit\Http\Requests\RequestTestCase;

/** @see InvoiceController */
class StoreRequestTest extends RequestTestCase
{
    use CanRefreshDatabase;

    protected string $requestClass = StoreRequest::class;

    /** @test */
    public function its_file_is_required()
    {
        $request = $this->formRequest($this->requestClass, []);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::FILE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::FILE);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_file_must_be_a_file()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::FILE => 'a string']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::FILE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::FILE);
        $request->assertValidationMessages([Lang::get('validation.file', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_file_must_be_a_file_with_allow_extension()
    {
        $file    = UploadedFile::fake()->create('test.txt');
        $request = $this->formRequest($this->requestClass, [RequestKeys::FILE => $file]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::FILE]);
        $attribute  = $this->getDisplayableAttribute(RequestKeys::FILE);
        $validTypes = ['jpg', 'jpeg', 'png', 'pdf', 'svg'];
        $request->assertValidationMessages([
            Lang::get('validation.mimes', ['attribute' => $attribute, 'values' => join(', ', $validTypes)]),
        ]);
    }

    /** @test */
    public function its_file_should_not_be_larger_than_three_megabytes()
    {
        $file    = UploadedFile::fake()->create('invoice.pdf')->size(1024 * 4);
        $request = $this->formRequest($this->requestClass, [RequestKeys::FILE => $file]);
        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::FILE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::FILE);
        $size      = 1024 * 3;
        $request->assertValidationMessages([
            Lang::get('validation.max.file', ['attribute' => $attribute, 'max' => $size]),
        ]);
    }

    /** @test */
    public function it_pass_on_valid_data()
    {
        $file    = UploadedFile::fake()->create('invoice.pdf')->size(1024);
        $request = $this->formRequest($this->requestClass, [RequestKeys::FILE => $file]);
        $request->assertValidationPassed();
    }
}
