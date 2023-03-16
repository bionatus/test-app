<?php

namespace App\Handlers;

use App\Models\Address;
use App\Models\CurriDelivery;
use App\Models\IsDeliverable;
use App\Models\Order;
use App\Models\OrderDelivery;
use Illuminate\Database\Eloquent\Relations\Relation;

class OrderDeliveryHandler
{
    protected Order          $order;
    protected ?OrderDelivery $oldOrderDelivery = null;
    protected ?OrderDelivery $newOrderDelivery = null;
    protected ?IsDeliverable $oldDeliveryType  = null;

    public function __construct(Order $order)
    {
        $this->order            = $order;
        $this->oldOrderDelivery = $order->orderDelivery;
        if ($this->oldOrderDelivery) {
            $this->oldDeliveryType = $this->oldOrderDelivery->deliverable;
        }
    }

    public function getType()
    {
        $type = $this->oldOrderDelivery->type;
        if ($this->newOrderDelivery) {
            $type = $this->newOrderDelivery->type;
        }

        return $type;
    }

    public function getOldDeliveryType()
    {
        return $this->oldDeliveryType;
    }

    public function createOrUpdateDelivery(array $data): OrderDelivery
    {
        /** @var OrderDelivery $orderDelivery */
        $orderDelivery          = $this->order->orderDelivery()
            ->updateOrCreate(['order_id' => $this->order->getKey()], $data);
        $this->newOrderDelivery = $orderDelivery;

        return $orderDelivery;
    }

    public function createOrUpdateOriginAddress(array $data): Address
    {
        $oldOriginAddress = null;
        if ($this->oldDeliveryType && $this->oldDeliveryType->hasOriginAddress()) {
            $oldOriginAddress = $this->oldDeliveryType->originAddress;
        }

        return $this->createOrUpdateAddress($data, $oldOriginAddress);
    }

    public function createOrUpdateDestinationAddress(array $data): Address
    {
        $oldDestinationAddress = null;
        if ($this->oldDeliveryType && $this->oldDeliveryType->hasDestinationAddress()) {
            $oldDestinationAddress = $this->oldDeliveryType->destinationAddress;
        }

        return $this->createOrUpdateAddress($data, $oldDestinationAddress);
    }

    public function createOrUpdateDeliveryType(
        Address $destinationAddress = null,
        Address $originAddress = null,
        array $dataTypeDelivery = []
    ): IsDeliverable {
        /** @var IsDeliverable $newDeliveryType */
        $newDeliveryType = $this->oldDeliveryType;
        $newData         = [];
        if (!$this->isTheSameDeliveryType()) {
            $newDeliveryTypeClass = Relation::morphMap()[$this->newOrderDelivery->type];
            $newDeliveryType      = new $newDeliveryTypeClass();
            $newData              = [$this->newOrderDelivery->getKeyName() => $this->newOrderDelivery->getKey()];
        }

        if ($newDeliveryType->usesDestinationAddress() && $destinationAddress) {
            $newData['destination_address_id'] = $destinationAddress->getKey();
        }

        if ($newDeliveryType->usesOriginAddress() && $originAddress) {
            $newData['origin_address_id'] = $originAddress->getKey();
        }

        if ($this->newOrderDelivery->isCurriDelivery()) {
            $newData['vehicle_type'] = $dataTypeDelivery['vehicle_type'] ?? CurriDelivery::VEHICLE_TYPE_CAR;
            if (isset($dataTypeDelivery['quote_id'])) {
                $newData['quote_id'] = $dataTypeDelivery['quote_id'];
            }
        }

        if ($this->newOrderDelivery->isShipmentDelivery()) {
            $newData['shipment_delivery_preference_id'] = $dataTypeDelivery['shipment_delivery_preference_id'] ?? null;
        }

        $newDeliveryType->fill($newData);
        $newDeliveryType->save();

        $this->clearRelationships();

        return $newDeliveryType;
    }

    protected function clearRelationships()
    {
        if (!$this->isTheSameDeliveryType() && $this->oldDeliveryType) {

            $oldOriginAddress = $oldDestinationAddress = null;

            if ($this->oldDeliveryType->hasOriginAddress()) {
                $oldOriginAddress = $this->oldDeliveryType->originAddress;
            }

            if ($this->oldDeliveryType->hasDestinationAddress()) {
                $oldDestinationAddress = $this->oldDeliveryType->destinationAddress;
            }

            $this->oldDeliveryType->delete();

            if (!$this->newOrderDelivery->deliverable->usesOriginAddress() && $this->oldDeliveryType->hasOriginAddress()) {
                $oldOriginAddress->delete();
            }

            if (!$this->newOrderDelivery->deliverable->usesDestinationAddress() && $this->oldDeliveryType->hasDestinationAddress()) {
                $oldDestinationAddress->delete();
            }
        }
    }

    protected function createOrUpdateAddress(array $data, Address $address = null): Address
    {
        $addressId = null;
        if ($address) {
            $addressId = $address->getKey();
        }

        return Address::updateOrCreate(['id' => $addressId], $data);
    }

    protected function isTheSameDeliveryType(): bool
    {
        if (!$this->oldOrderDelivery) {
            return false;
        }

        return $this->oldOrderDelivery->type === $this->newOrderDelivery->type;
    }
}
