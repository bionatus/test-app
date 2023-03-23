<?php /** @noinspection DuplicatedCode */

use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Http\Controllers\Api\V4\Account\Cart\CartItemController;
use App\Http\Controllers\Api\V4\Account\CartController;
use App\Http\Controllers\Api\V4\Account\CompanyController as AccountCompanyController;
use App\Http\Controllers\Api\V4\Account\ProfileController;
use App\Http\Controllers\Api\V4\Account\PubnubChannelController;
use App\Http\Controllers\Api\V4\Account\Supplier\OrderController as SupplierOrderController;
use App\Http\Controllers\Api\V4\CompanyController;
use App\Http\Controllers\Api\V4\CustomItemController;
use App\Http\Controllers\Api\V4\Order\Active\OrderController as ActiveOrdersController;
use App\Http\Controllers\Api\V4\Order\ApproveController;
use App\Http\Controllers\Api\V4\Order\CancelController;
use App\Http\Controllers\Api\V4\Order\CompleteController;
use App\Http\Controllers\Api\V4\Order\ConfirmTotalController;
use App\Http\Controllers\Api\V4\Order\Delivery\Address\AddressController;
use App\Http\Controllers\Api\V4\Order\Delivery\Pickup\ConfirmController;
use App\Http\Controllers\Api\V4\Order\Delivery\Shipment\ApproveController as ApproveShipmentController;
use App\Http\Controllers\Api\V4\Order\DeliveryController;
use App\Http\Controllers\Api\V4\Order\ItemOrder\ExtraItemController;
use App\Http\Controllers\Api\V4\Order\ItemOrderController;
use App\Http\Controllers\Api\V4\Order\NameController;
use App\Http\Controllers\Api\V4\OrderController;
use App\Http\Controllers\Api\V4\Supplier\DefaultSupplierController;
use App\Http\Controllers\Api\V4\Supplier\NewMessageController;
use App\Http\Controllers\Api\V4\SupplierController;
use App\Http\Controllers\Api\V4\Supply\SearchController as SupplySearchController;
use App\Http\Controllers\Api\V4\SupplyCategory\SupplySubcategoryController;
use App\Http\Controllers\Api\V4\SupplyCategoryController;
use App\Http\Controllers\Api\V4\SupplyController;
use App\Http\Controllers\Api\V4\SupportCallController;
use App\Http\Middleware\AuthenticateUser;
use App\Http\Middleware\ValidatePointsOnSupportCall;
use App\Http\Middleware\VerifyCartNotEmpty;
use Illuminate\Routing\Router;

Route::middleware(AuthenticateUser::class)->group(function(Router $route) {

    $route->prefix('account')->group(function(Router $route) {

        $route->prefix('cart')->group(function(Router $route) {
            $route->get('/', [CartController::class, 'show'])->name(RouteNames::API_V4_ACCOUNT_CART_SHOW);
            $route->post('/', [CartController::class, 'store'])->name(RouteNames::API_V4_ACCOUNT_CART_STORE);
            $route->patch('/', [CartController::class, 'update'])->name(RouteNames::API_V4_ACCOUNT_CART_UPDATE);
            $route->delete('/', [CartController::class, 'delete'])->name(RouteNames::API_V4_ACCOUNT_CART_DELETE);

            $route->prefix('items')->group(function(Router $route) {
                $route->get('/', [CartItemController::class, 'index'])
                    ->name(RouteNames::API_V4_ACCOUNT_CART_ITEM_INDEX);
                $route->post('/', [CartItemController::class, 'store'])
                    ->name(RouteNames::API_V4_ACCOUNT_CART_ITEM_STORE);
                $route->prefix('{' . RouteParameters::CART_ITEM . '}')->group(function(Router $route) {
                    $route->patch('/', [CartItemController::class, 'update'])
                        ->name(RouteNames::API_V4_ACCOUNT_CART_ITEM_UPDATE)
                        ->middleware('can:update,' . RouteParameters::CART_ITEM);
                    $route->delete('/', [CartItemController::class, 'delete'])
                        ->name(RouteNames::API_V4_ACCOUNT_CART_ITEM_DELETE)
                        ->middleware('can:delete,' . RouteParameters::CART_ITEM);
                });
            });
        });

        $route->prefix('channels')->group(function(Router $route) {
            $route->get('/', [PubnubChannelController::class, 'index'])->name(RouteNames::API_V4_ACCOUNT_CHANNEL_INDEX);
        });

        $route->prefix('companies')->group(function(Router $route) {
            $route->post('/', [AccountCompanyController::class, 'store'])
                ->name(RouteNames::API_V4_ACCOUNT_COMPANY_STORE);
            $route->patch('/', [AccountCompanyController::class, 'update'])
                ->name(RouteNames::API_V4_ACCOUNT_COMPANY_UPDATE);
        });

        $route->prefix('profile')->group(function(Router $route) {
            $route->get('/', [ProfileController::class, 'show'])->name(RouteNames::API_V4_ACCOUNT_PROFILE_SHOW);
            $route->patch('/', [ProfileController::class, 'update'])->name(RouteNames::API_V4_ACCOUNT_PROFILE_UPDATE);
        });

        $route->prefix('suppliers')->group(function(Router $route) {
            $route->prefix('{' . RouteParameters::SUPPLIER . '}')->group(function(Router $route) {
                $route->get('orders', [SupplierOrderController::class, 'index'])
                    ->name(RouteNames::API_V4_ACCOUNT_SUPPLIER_ORDER_INDEX);
            });
        });
    });

    $route->prefix('companies')->group(function(Router $route) {
        $route->get('/', [CompanyController::class, 'index'])->name(RouteNames::API_V4_COMPANY_INDEX);
    });

    $route->post('custom-items', CustomItemController::class)->name(RouteNames::API_V4_CUSTOM_ITEM_STORE);

    $route->prefix('orders')->group(function(Router $route) {
        $route->get('/', [OrderController::class, 'index'])->name(RouteNames::API_V4_ORDER_INDEX);

        $route->prefix('active')->group(function(Router $route) {
            $route->get('/', [ActiveOrdersController::class, 'index'])->name(RouteNames::API_V4_ORDER_ACTIVE_INDEX);
        });

        $route->post('/', [OrderController::class, 'store'])
            ->name(RouteNames::API_V4_ORDER_STORE)
            ->middleware(VerifyCartNotEmpty::class);

        $route->prefix('/{' . RouteParameters::ORDER . '}')->group(function(Router $route) {

            $route->post('approve', ApproveController::class)
                ->name(RouteNames::API_V4_ORDER_APPROVE_STORE)
                ->middleware('can:approve,' . RouteParameters::ORDER);

            $route->post('name', NameController::class)
                ->name(RouteNames::API_V4_ORDER_NAME_UPDATE)
                ->middleware('can:updateName,' . RouteParameters::ORDER);

            $route->get('/', [OrderController::class, 'show'])
                ->name(RouteNames::API_V4_ORDER_SHOW)
                ->middleware('can:read,' . RouteParameters::ORDER);
            $route->post('cancel', CancelController::class)
                ->name(RouteNames::API_V4_ORDER_CANCEL_STORE)
                ->middleware('can:cancel,' . RouteParameters::ORDER);
            $route->post('complete', CompleteController::class)
                ->name(RouteNames::API_V4_ORDER_COMPLETE_STORE)
                ->middleware('can:complete,' . RouteParameters::ORDER);
            $route->post('confirm-total', ConfirmTotalController::class)
                ->name(RouteNames::API_V4_ORDER_CONFIRM_TOTAL_STORE)
                ->middleware('can:confirmTotal,' . RouteParameters::ORDER);

            $route->prefix('delivery')->group(function(Router $route) {
                $route->post('/', [DeliveryController::class, 'store'])
                    ->name(RouteNames::API_V4_ORDER_DELIVERY_STORE)
                    ->middleware('can:createDelivery,' . RouteParameters::ORDER);
                $route->post('address', [AddressController::class, 'store'])
                    ->name(RouteNames::API_V4_ORDER_DELIVERY_ADDRESS_STORE);
                $route->prefix('pickup')->group(function(Router $route) {
                    $route->post('confirm', ConfirmController::class)
                        ->name(RouteNames::API_V4_ORDER_DELIVERY_PICKUP_CONFIRM_STORE)
                        ->middleware('can:confirmPickup,' . RouteParameters::ORDER);
                });
                $route->prefix('shipment')->group(function(Router $route) {
                    $route->post('approve', ApproveShipmentController::class)
                        ->name(RouteNames::API_V4_ORDER_DELIVERY_SHIPMENT_APPROVE_STORE)
                        ->middleware('can:approveShipment,' . RouteParameters::ORDER);
                });
            });

            $route->post('extra-items', [ExtraItemController::class, 'store'])
                ->name(RouteNames::API_V4_ORDER_ITEM_ORDER_EXTRA_ITEM_STORE)
                ->middleware('can:updateItemOrder,' . RouteParameters::ORDER);

            $route->prefix('items')->group(function(Router $route) {
                $route->get('/', [ItemOrderController::class, 'index'])
                    ->name(RouteNames::API_V4_ORDER_ITEM_ORDER_INDEX)
                    ->middleware('can:read,' . RouteParameters::ORDER);
            });
        });
    });

    $route->prefix('suppliers')->group(function(Router $route) {
        $route->get('default', DefaultSupplierController::class)->name(RouteNames::API_V4_SUPPLIER_DEFAULT_SHOW);

        $route->prefix('/{' . RouteParameters::SUPPLIER . '}')->group(function(Router $route) {
            $route->get('/', [SupplierController::class, 'show'])->name(RouteNames::API_V4_SUPPLIER_SHOW);
            $route->post('new-message', NewMessageController::class)->name(RouteNames::API_V4_SUPPLIER_NEW_MESSAGE);
        });
    });

    $route->prefix('supplies')->group(function(Router $route) {
        $route->get('/', [SupplyController::class, 'index'])->name(RouteNames::API_V4_SUPPLY_INDEX);
        $route->get('search', SupplySearchController::class)->name(RouteNames::API_V4_SUPPLY_SEARCH_INDEX);
    });

    $route->prefix('supply-categories')->group(function(Router $route) {
        $route->get('/', [SupplyCategoryController::class, 'index'])->name(RouteNames::API_V4_SUPPLY_CATEGORY_INDEX);

        $route->prefix('/{' . RouteParameters::SUPPLY_CATEGORY . '}')->group(function(Router $route) {
            $route->prefix('subcategories')->group(function(Router $route) {
                $route->get('/', [SupplySubcategoryController::class, 'index'])
                    ->name(RouteNames::API_V4_SUPPLY_CATEGORY_SUBCATEGORY_INDEX);
            });
        });
    });

    $route->prefix('support-calls')->group(function(Router $route) {
        $route->post('/', [SupportCallController::class, 'store'])
            ->name(RouteNames::API_V4_SUPPORT_CALL_STORE)
            ->middleware(ValidatePointsOnSupportCall::class);
    });
});
