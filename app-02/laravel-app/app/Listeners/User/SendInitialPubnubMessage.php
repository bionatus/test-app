<?php

namespace App\Listeners\User;

use App;
use App\Actions\Models\Supplier\PublishMessage;
use App\Constants\PubnubMessageTypes;
use App\Events\PubnubChannel\Created;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Lang;

class SendInitialPubnubMessage implements ShouldQueue
{
    use InteractsWithQueue;

    const PLACEHOLDER_SUPPLIER_ADDRESS = 'supplier_address';
    const PLACEHOLDER_SUPPLIER_CITY    = 'supplier_city';
    const PLACEHOLDER_SUPPLIER_NAME    = 'supplier_name';
    const PLACEHOLDER_USER_NAME        = 'user_name';
    public string $connection = 'database';

    public function handle(Created $event)
    {
        $pubnubChannel   = $event->pubnubChannel();
        $message         = PubnubMessageTypes::INITIAL_MESSAGE;
        $message['text'] = $this->generateMessage($message['text'], $pubnubChannel->user, $pubnubChannel->supplier);

        App::make(PublishMessage::class, [
            'message'       => $message,
            'pubnubChannel' => $pubnubChannel,
            'supplier'      => $pubnubChannel->supplier,
        ])->execute();
    }

    private function generateMessage($message, $user, $supplier): string
    {
        return Lang::get($message, [
            self::PLACEHOLDER_SUPPLIER_ADDRESS => $supplier->address,
            self::PLACEHOLDER_SUPPLIER_CITY    => $supplier->city,
            self::PLACEHOLDER_SUPPLIER_NAME    => $supplier->name,
            self::PLACEHOLDER_USER_NAME        => $user->first_name,
        ]);
    }
}
