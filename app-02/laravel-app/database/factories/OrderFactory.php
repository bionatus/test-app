<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Oem;
use App\Models\Order;
use App\Models\OrderStaff;
use App\Models\OrderSubstatus;
use App\Models\Staff;
use App\Models\Substatus;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|Order create($attributes = [], ?Model $parent = null)
 * @method Collection|Order createQuietly($attributes = [], ?Model $parent = null)
 * @method Collection|Order make($attributes = [], ?Model $parent = null)
 */
class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'uuid'        => $this->faker->unique()->uuid,
            'user_id'     => User::factory(),
            'supplier_id' => Supplier::factory()->createQuietly(),
        ];
    }

    public function usingUser(User $user): self
    {
        return $this->state(function() use ($user) {
            return [
                'user_id' => $user,
            ];
        });
    }

    public function usingSupplier(Supplier $supplier): self
    {
        return $this->state(function() use ($supplier) {
            return [
                'supplier_id' => $supplier,
            ];
        });
    }

    public function usingStaff(Staff $staff): self
    {
        return $this->afterCreating(function($order) use ($staff) {
            OrderStaff::factory()->usingOrder($order)->usingStaff($staff)->create();
        });
    }

    public function usingOem(Oem $oem): self
    {
        return $this->state(function() use ($oem) {
            return [
                'oem_id' => $oem,
            ];
        });
    }

    public function approved(): self
    {
        return $this->afterCreating(function($order) {
            OrderSubstatus::factory()
                ->usingOrder($order)
                ->usingSubstatusId(Substatus::STATUS_APPROVED_AWAITING_DELIVERY)
                ->createQuietly();
        });
    }

    public function pending(): self
    {
        return $this->afterCreating(function($order) {
            OrderSubstatus::factory()
                ->usingOrder($order)
                ->usingSubstatusId(Substatus::STATUS_PENDING_REQUESTED)
                ->createQuietly();
        });
    }

    public function pendingApproval(): self
    {
        return $this->afterCreating(function($order) {
            OrderSubstatus::factory()
                ->usingOrder($order)
                ->usingSubstatusId(Substatus::STATUS_PENDING_APPROVAL_FULFILLED)
                ->createQuietly();
        });
    }

    public function completed(): self
    {
        return $this->afterCreating(function($order) {
            OrderSubstatus::factory()
                ->usingOrder($order)
                ->usingSubstatusId(Substatus::STATUS_COMPLETED_DONE)
                ->createQuietly();
        });
    }

    public function canceled(): self
    {
        return $this->afterCreating(function($order) {
            OrderSubstatus::factory()
                ->usingOrder($order)
                ->usingSubstatusId(Substatus::STATUS_CANCELED_DECLINED)
                ->createQuietly();
        });
    }

    public function usingSubstatus(Substatus $substatus): self
    {
        return $this->afterCreating(function($order) use ($substatus) {
            OrderSubstatus::factory()->usingOrder($order)->usingSubstatus($substatus)->createQuietly();
        });
    }

    public function usingCompany(Company $company): self
    {
        return $this->state(function() use ($company) {
            return [
                'company_id' => $company,
            ];
        });
    }
}
