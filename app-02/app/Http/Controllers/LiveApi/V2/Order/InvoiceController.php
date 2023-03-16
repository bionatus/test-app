<?php

namespace App\Http\Controllers\LiveApi\V2\Order;

use App;
use App\Constants\MediaCollectionNames;
use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\LiveApi\V2\Order\Invoice\StoreRequest;
use App\Http\Resources\LiveApi\V2\Order\DetailedResource;
use App\Models\Order;
use Response;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class InvoiceController extends Controller
{
    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function store(StoreRequest $request, Order $order)
    {
        $order->addMediaFromRequest(RequestKeys::FILE)->toMediaCollection(MediaCollectionNames::INVOICE);

        return (new DetailedResource($order))->response()->setStatusCode(HttpResponse::HTTP_CREATED);
    }

    public function delete(Order $order)
    {
        $order->clearMediaCollection(MediaCollectionNames::INVOICE);

        return Response::noContent();
    }
}
