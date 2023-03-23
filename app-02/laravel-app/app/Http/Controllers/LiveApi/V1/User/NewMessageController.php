<?php

namespace App\Http\Controllers\LiveApi\V1\User;

use App;
use App\Actions\Models\PubnubChannel\GetPubnubChannel;
use App\Actions\Models\Supplier\PublishMessage;
use App\Constants\PubnubMessageTypes;
use App\Constants\RequestKeys;
use App\Events\User\NewMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\LiveApi\V1\User\NewMessage\InvokeRequest;
use App\Models\User;
use Auth;
use Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class NewMessageController extends Controller
{
    public function __invoke(InvokeRequest $request, User $user)
    {
        $supplier      = Auth::user()->supplier;
        $pubnubChannel = App::make(GetPubnubChannel::class, ['supplier' => $supplier, 'user' => $user])->execute();

        $message         = PubnubMessageTypes::TEXT;
        $message['text'] = $request->get(RequestKeys::MESSAGE);

        App::make(PublishMessage::class, [
            'message'       => $message,
            'pubnubChannel' => $pubnubChannel,
            'supplier'      => $supplier,
        ])->execute();

        NewMessage::dispatch($supplier, $user, $message['text']);

        return Response::noContent(HttpResponse::HTTP_CREATED);
    }
}
