<?php

namespace Tests\Feature\Api\V3\Technician;

use App\Constants\Filesystem;
use App\Constants\RouteNames;
use App\Http\Controllers\Api\V3\TechniciansController;
use App\Http\Resources\Api\V3\Technician\BaseResource;
use App\Models\Technician;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Storage;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see TechniciansController */
class ShowTest extends TestCase
{
    use RefreshDatabase;

    private string $routeName = RouteNames::API_V3_SUPPORT_TECHNICIAN_SHOW;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->get(URL::route($this->routeName));
    }

    /** @test */
    public function it_displays_technicians()
    {
        Storage::fake(Filesystem::DISK_MEDIA);
        $technicians = Technician::factory()->count(10)->create();
        $route       = URL::route($this->routeName);

        $this->login();

        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), false), $response);

        $data = Collection::make($response->json('data'));

        $data->each(function(array $rawTechnician) use ($technicians) {
            $technician = $technicians->where('id', $rawTechnician['id'])->first();

            $this->assertSame($technician->show_in_app, true);
        });
    }
}
