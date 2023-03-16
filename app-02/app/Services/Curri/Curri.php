<?php

namespace App\Services\Curri;

use App;
use App\Exceptions\CurriException;
use App\Models\Address;
use App\Models\CurriDelivery;
use App\Models\OrderDelivery;
use Carbon\Exceptions\InvalidFormatException;
use Config;
use GraphQL\Client;
use GraphQL\Exception\QueryError;
use GraphQL\Mutation;
use GraphQL\Query;
use GraphQL\RawObject;
use Illuminate\Support\Carbon;

class Curri
{
    private Client  $client;
    private ?string $userId;
    private ?string $apiKey;
    private ?string $endpoint;

    /**
     * @throws CurriException
     */
    public function __construct()
    {
        $this->userId   = Config::get('curri.user_id');
        $this->apiKey   = Config::get('curri.api_key');
        $this->endpoint = Config::get('curri.api_endpoint');

        if (!$this->userId || !$this->apiKey) {
            throw new CurriException('Credentials are required to create a Client');
        }

        if (!$this->endpoint) {
            throw new CurriException('Endpoint is required to create a Client');
        }

        $token        = base64_encode(sprintf('%s:%s', $this->userId, $this->apiKey));
        $this->client = App::make(Client::class, [
            'endpointUrl'          => $this->endpoint,
            'authorizationHeaders' => [
                'Authorization' => 'Basic ' . $token,
            ],
        ]);
    }

    /**
     * @throws CurriException
     */
    public function getQuote(Address $destinationAddress, Address $originAddress, string $method)
    {
        $origin = sprintf('{
            name: "%s",
            addressLine1: "%s",
            addressLine2: "%s",
            city: "%s",
            state: "%s",
            postalCode: "%s"
        }', $originAddress->address_1, $originAddress->address_1, $originAddress->address_2, $originAddress->city,
            $originAddress->state, $originAddress->zip_code);

        $destination = sprintf('{
            name: "%s",
            addressLine1: "%s",
            city: "%s",
            state: "%s",
            postalCode: "%s"
        }', $destinationAddress->address_1, $destinationAddress->address_1, $destinationAddress->city,
            $destinationAddress->state, $destinationAddress->zip_code);

        $gql = (new Query('deliveryQuote'))->setArguments([
            'origin'         => new RawObject($origin),
            'destination'    => new RawObject($destination),
            'priority'       => 'scheduled',
            'deliveryMethod' => $method,
        ])->setSelectionSet([
            'id',
            'fee',
            'distance',
            'duration',
            'pickupDuration',
            'deliveryMethod',
        ]);
        try {
            $result = $this->client->runQuery($gql);

            return [
                'fee'     => $result->getData()->deliveryQuote->fee,
                'quoteId' => $result->getData()->deliveryQuote->id,
            ];
        } catch (QueryError $exception) {
            throw new CurriException($exception->getMessage());
        }
    }

    /**
     * @throws CurriException
     */
    public function bookDelivery(OrderDelivery $delivery)
    {
        $date = $delivery->date;
        $time = $delivery->time_range;

        if (!$date || !$time) {
            throw new CurriException('Delivery date and time are required');
        }

        $order    = $delivery->order;
        $supplier = $order->supplier;
        $timezone = $supplier->timezone;
        $hours    = explode(' - ', $time);
        $fullDate = $date->format('Y-m-d') . ' ' . $hours[0];

        try {
            $formattedDate = Carbon::createFromFormat('Y-m-d gA', $fullDate, $timezone);
            if ($formattedDate->lte(Carbon::now()) && $formattedDate->copy()->addMinutes(150)->gte(Carbon::now())) {
                $minutesToAdd  = Carbon::now()->addMinute()->format('i');
                $formattedDate = $formattedDate->addMinutes($minutesToAdd);
            }
        } catch (InvalidFormatException $exception) {
            throw new CurriException('Invalid delivery date or time format');
        }

        if ($formattedDate->addMinute()->isPast()) {
            throw new CurriException('Delivery date is in the past');
        }

        /**@var CurriDelivery $deliverable */
        $deliverable     = $delivery->deliverable;
        $user            = $delivery->order->user;
        $origin          = $deliverable->originAddress;
        $destination     = $deliverable->destinationAddress;
        $totalLineItems  = $order->activeItemOrders()->count();
        $accountantEmail = ($accountant = $supplier->accountant) ? $accountant->email : '';
        $technicianName  = $user->fullName();

        $dropOffNote = sprintf('Tech name: %s.\nContractor : %s.\nOther Tech Delivery Instructions: %s',
            $technicianName, $user->companyName(), $delivery->note);

        $pickUpNote = sprintf('Supplier Name: %s.\nBid #: %s.\nPo #: %s.\nTech: %s.\nContractor: %s\n# of line items: %s',
            $supplier->name, $order->bid_number, $order->name, $technicianName, $user->companyName(), $totalLineItems);

        $customerData = sprintf('customerData: {
            supplierName: "%s"
            bidNumber: "%s"
            poNumber: "%s"
            lineItems: "%s"
            bluonDistributorId: "%s"
            accountingEmail: "%s"
        }', $supplier->name, $order->bid_number, $order->name, $totalLineItems, $supplier->getKey(), $accountantEmail);

        $deliveryMeta = sprintf('deliveryMeta: {
                poNumber: "%s"
                orderNumber: "%s"
                pickupNote: "%s"
                dropoffNote: "%s"
                %s
            }', $order->name, $order->bid_number, $pickUpNote, $dropOffNote, $customerData);

        $data = sprintf('{
            skipQuote: true,
            origin: {
                name: "%s"
                addressLine1: "%s"
                addressLine2: "%s"
                city: "%s"
                state: "%s"
                postalCode: %d
            }
            destination: {
                name: "%s"
                addressLine1: "%s"
                addressLine2: "%s"
                city: "%s"
                state: "%s"
                postalCode: %d
            }
            dropoffContact: {
                name: "%s"
                phoneNumber: "%s"
            }
            pickupContact: {
                name: "%s"
                phoneNumber: "%s"
            }
            %s
            priority: "scheduled"
            scheduledAt: "%s"
            deliveryMethod: "%s"
        }', $supplier->name, $origin->address_1, $origin->address_2, $origin->city, $origin->state, $origin->zip_code,
            $technicianName, $destination->address_1, $destination->address_2, $destination->city, $destination->state,
            $destination->zip_code, $technicianName, $user->getPhone(), $supplier->contact_name, $supplier->phone,
            $deliveryMeta, $formattedDate->toIso8601ZuluString(), $deliverable->vehicle_type);

        $mutation = (new Mutation('bookDelivery'))->setArguments([
            'data' => new RawObject($data),
        ])->setSelectionSet([
            'id',
            'price',
            'createdAt',
            'deliveryMethod',
            'scheduledAt',
            'trackingId',
        ]);

        try {
            $result = $this->client->runQuery($mutation);

            return [
                'id'          => $result->getData()->bookDelivery->id,
                'price'       => $result->getData()->bookDelivery->price,
                'tracking_id' => $result->getData()->bookDelivery->trackingId,
            ];
        } catch (QueryError $exception) {
            throw new CurriException($exception->getMessage());
        }
    }
}
