<?php

namespace App\Http\Controllers\Api\V3\Supplier;

use App;
use App\Actions\Models\PubnubChannel\GetPubnubChannel;
use App\Actions\Models\User\PublishMessage;
use App\Constants\PubnubMessageTypes;
use App\Constants\RequestKeys;
use App\Events\Supplier\NewMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V3\Supplier\NewMessage\InvokeRequest;
use App\Models\Supplier;
use Auth;
use Illuminate\Support\Carbon;
use Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class NewMessageController extends Controller
{
    public function __invoke(InvokeRequest $request, Supplier $supplier)
    {
        $user = Auth::user();

        $pubnubChannel = App::make(GetPubnubChannel::class, ['supplier' => $supplier, 'user' => $user])->execute();
        $pubnubChannel->update(['user_last_message_at' => Carbon::now()]);

        $message         = PubnubMessageTypes::TEXT;
        $message['text'] = $request->get(RequestKeys::MESSAGE);

        App::make(PublishMessage::class, [
            'message'       => $message,
            'pubnubChannel' => $pubnubChannel,
            'user'          => $user,
        ])->execute();

        NewMessage::dispatch($supplier, $user, $message['text']);

        return Response::noContent(HttpResponse::HTTP_CREATED);
    }
}
