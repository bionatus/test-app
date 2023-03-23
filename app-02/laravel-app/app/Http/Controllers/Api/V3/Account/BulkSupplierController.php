<?php

namespace App\Http\Controllers\Api\V3\Account;

use App;
use App\Actions\Models\Activity\BuildProperty;
use App\Actions\Models\PubnubChannel\GetPubnubChannel;
use App\Constants\RequestKeys;
use App\Events\PubnubChannel\Created;
use App\Events\Supplier\Selected;
use App\Events\Supplier\Unselected;
use App\Events\User\SuppliersUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V3\Account\BulkSupplier\InvokeRequest;
use App\Http\Resources\Api\V3\Account\BulkSupplier\BaseResource;
use App\Jobs\LogActivity;
use App\Models\Activity;
use App\Models\Order\Scopes\BySupplier;
use App\Models\Supplier;
use App\Models\Supplier\Scopes\ByPreferred;
use App\Models\SupplierUser;
use Auth;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;

class BulkSupplierController extends Controller
{
    public function __invoke(InvokeRequest $request)
    {
        $user            = Auth::user();
        $suppliers       = $request->suppliers();
        $supplierKeyName = Supplier::keyName();

        $visibleSupplierUsers = $user->visibleSupplierUsers()->get();
        $suppliersToAttach    = $suppliers->whereNotIn($supplierKeyName, $visibleSupplierUsers->pluck('supplier_id'));
        $suppliersToDetach    = $visibleSupplierUsers->whereNotIn('supplier_id', $suppliers->pluck('id'));

        $changes = Collection::make([
            'attached' => new Collection(),
            'detached' => new Collection(),
        ]);

        $supplierUsers = $user->supplierUsers();
        $oldPreferred  = $supplierUsers->scoped(new ByPreferred())->first();
        $supplierUsers->update(['preferred' => null]);

        $suppliersToAttach->each(function(Supplier $supplierToAttach) use ($changes, $user) {
            $newSupplierUser = SupplierUser::updateOrCreate([
                'user_id'     => $user->getKey(),
                'supplier_id' => $supplierToAttach->getKey(),
            ], ['visible_by_user' => true, 'preferred' => null]);

            if ($newSupplierUser->wasRecentlyCreated) {
                $property = (new BuildProperty('name', $supplierToAttach->name))->execute();
                LogActivity::dispatch(Activity::ACTION_CREATED, Activity::RESOURCE_PROFILE, $supplierToAttach,
                    Auth::getUser(), $property, Activity::TYPE_PROFILE);

                $changes->get('attached')->push($supplierToAttach);
            }
        });

        $suppliersToDetach->each(function(SupplierUser $supplierToDetach) use ($changes, $user) {
            $supplier = $supplierToDetach->supplier;
            $orders   = $user->orders()->scoped(new BySupplier($supplier))->count();

            if ($orders || $supplierToDetach->customer_tier || $supplierToDetach->cash_buyer) {
                $supplierToDetach->update(['visible_by_user' => false]);
            } else {
                $changes->get('detached')->push($supplierToDetach->supplier);
                $supplierToDetach->delete();

                $property = (new BuildProperty('name', $supplier->name))->execute();
                LogActivity::dispatch(Activity::ACTION_DELETED, Activity::RESOURCE_PROFILE, $supplier, Auth::getUser(),
                    $property, Activity::TYPE_PROFILE);
            }
        });

        $changedCount      = $changes->sum(fn(Collection $mode) => count($mode));
        $attachedSuppliers = $changes->get('attached');

        $attachedSuppliers->each(function(Supplier $supplier) use ($user) {
            if ($supplier->published_at) {
                $pubnubChannel = $user->pubnubChannels()->scoped(new BySupplier($supplier))->first();

                if (!$pubnubChannel) {
                    $pubnubChannel = App::make(GetPubnubChannel::class, ['supplier' => $supplier, 'user' => $user])
                        ->execute();

                    Created::dispatch($pubnubChannel);
                }
            }
        });

        if ($preferred = $suppliers->where(Supplier::routeKeyName(), $request->get(RequestKeys::PREFERRED))->first()) {
            $user->suppliers()->updateExistingPivot($preferred->id, ['preferred' => true]);

            if (null === $oldPreferred || $oldPreferred->supplier->getKey() != $preferred->getKey()) {
                $property = (new BuildProperty('name', $preferred->name))->execute();
                LogActivity::dispatch(Activity::ACTION_UPDATED, Activity::RESOURCE_PROFILE, $preferred, Auth::getUser(),
                    $property, Activity::TYPE_PROFILE);
            }
        }

        if ($changedCount) {
            SuppliersUpdated::dispatch($user);
        }

        if ($changes->get('attached')) {
            $suppliersToAttach = $changes->get('attached');
            $suppliersToAttach->each(function(Supplier $supplier) {
                Selected::dispatch($supplier);
            });
        }

        if ($changes->get('detached')) {
            $detachedSuppliers = $changes->get('detached');
            $detachedSuppliers->each(function(Supplier $supplier) {
                Unselected::dispatch($supplier);
            });
        }

        return BaseResource::collection($user->visibleSuppliers()->paginate())
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }
}
