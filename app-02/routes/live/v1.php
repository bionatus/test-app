<?php

use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Http\Controllers\LiveApi\V1\Address\Country\StateController;
use App\Http\Controllers\LiveApi\V1\Address\CountryController;
use App\Http\Controllers\LiveApi\V1\AppSettingController;
use App\Http\Controllers\LiveApi\V1\Auth\Email\ForgotPasswordController;
use App\Http\Controllers\LiveApi\V1\Auth\Email\InitialPasswordController;
use App\Http\Controllers\LiveApi\V1\Auth\Email\LoginController;
use App\Http\Controllers\LiveApi\V1\Auth\Email\ResetPasswordController;
use App\Http\Controllers\LiveApi\V1\Brand\Series\OemController as BrandSeriesOemController;
use App\Http\Controllers\LiveApi\V1\Brand\SeriesController;
use App\Http\Controllers\LiveApi\V1\BrandController;
use App\Http\Controllers\LiveApi\V1\ConfirmedUserController;
use App\Http\Controllers\LiveApi\V1\LimitedSupplierController;
use App\Http\Controllers\LiveApi\V1\NotificationSettingController;
use App\Http\Controllers\LiveApi\V1\Oem\PartController as OemPartController;
use App\Http\Controllers\LiveApi\V1\OemController;
use App\Http\Controllers\LiveApi\V1\Order\AssignController;
use App\Http\Controllers\LiveApi\V1\Order\CancelController;
use App\Http\Controllers\LiveApi\V1\Order\CompleteController;
use App\Http\Controllers\LiveApi\V1\Order\CustomItemController;
use App\Http\Controllers\LiveApi\V1\Order\Delivery\UpdateEtaController;
use App\Http\Controllers\LiveApi\V1\Order\DeliveryController;
use App\Http\Controllers\LiveApi\V1\Order\FeeController;
use App\Http\Controllers\LiveApi\V1\Order\InProgress\CancelController as InProgressCancelController;
use App\Http\Controllers\LiveApi\V1\Order\InProgress\Delivery\Curri\ConfirmController;
use App\Http\Controllers\LiveApi\V1\Order\InProgress\Delivery\Curri\Notice\EnRoute\ConfirmController as NoticeEnRouteConfirmController;
use App\Http\Controllers\LiveApi\V1\Order\InProgress\Delivery\Curri\PriceController as InProgressPriceController;
use App\Http\Controllers\LiveApi\V1\Order\InProgress\DeliveryController as InProgressDeliveryController;
use App\Http\Controllers\LiveApi\V1\Order\InProgress\ItemOrder\RemoveController;
use App\Http\Controllers\LiveApi\V1\Order\InProgress\ItemOrderController as InProgressItemOrderController;
use App\Http\Controllers\LiveApi\V1\Order\InProgressController;
use App\Http\Controllers\LiveApi\V1\Order\ItemOrder\ReplacementController as ItemOrderReplacementController;
use App\Http\Controllers\LiveApi\V1\Order\ItemOrderController;
use App\Http\Controllers\LiveApi\V1\Order\PreApprovalController;
use App\Http\Controllers\LiveApi\V1\Order\ReopenController;
use App\Http\Controllers\LiveApi\V1\Order\SendForApprovalController;
use App\Http\Controllers\LiveApi\V1\Part\RecommendedReplacementController;
use App\Http\Controllers\LiveApi\V1\Part\ReplacementController;
use App\Http\Controllers\LiveApi\V1\PartController;
use App\Http\Controllers\LiveApi\V1\RemovedUserController;
use App\Http\Controllers\LiveApi\V1\Setting\BulkNotificationController;
use App\Http\Controllers\LiveApi\V1\SettingController;
use App\Http\Controllers\LiveApi\V1\Supplier\BulkBrandController;
use App\Http\Controllers\LiveApi\V1\Supplier\BulkHourController;
use App\Http\Controllers\LiveApi\V1\Supplier\UserController as CustomerController;
use App\Http\Controllers\LiveApi\V1\SupplierController;
use App\Http\Controllers\LiveApi\V1\Unauthenticated\Order\ApproveController as UnauthenticatedApproveController;
use App\Http\Controllers\LiveApi\V1\Unauthenticated\Order\ItemOrderController as UnauthenticatedItemOrderController;
use App\Http\Controllers\LiveApi\V1\Unauthenticated\OrderController as UnauthenticatedOrderController;
use App\Http\Controllers\LiveApi\V1\User\ConfirmedUserController as ConfirmedSupplierUserController;
use App\Http\Controllers\LiveApi\V1\User\NewMessageController;
use App\Http\Controllers\LiveApi\V1\User\OrderController;
use App\Http\Controllers\LiveApi\V1\UserController;
use App\Http\Middleware\AuthenticateStaff;
use App\Http\Middleware\HasSetInitialPassword;
use App\Models\ItemOrder;
use App\Models\Series;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Routing\Router;

Route::prefix('auth')->group(function(Router $route) {
    $route->post('email/login', LoginController::class)->name(RouteNames::LIVE_API_V1_AUTH_EMAIL_LOGIN);
    $route->post('email/initial-password', InitialPasswordController::class)->middleware([
        AuthenticateStaff::class,
        'can:setInitialPassword,' . Staff::class,
    ])->name(RouteNames::LIVE_API_V1_AUTH_EMAIL_INITIAL_PASSWORD);

    $route->post('email/forgot-password', [ForgotPasswordController::class, 'store'])
        ->name(RouteNames::LIVE_API_V1_AUTH_EMAIL_FORGOT_PASSWORD_STORE);
    $route->post('email/reset-password', [ResetPasswordController::class, 'store'])
        ->name(RouteNames::LIVE_API_V1_AUTH_EMAIL_RESET_PASSWORD_STORE);
});

Route::middleware([AuthenticateStaff::class])->group(function(Router $route) {
    $route->prefix('limited-supplier')->group(function(Router $route) {
        $route->get('/', [LimitedSupplierController::class, 'show'])
            ->name(RouteNames::LIVE_API_V1_LIMITED_SUPPLIER_SHOW);
    });
});

Route::middleware([AuthenticateStaff::class, HasSetInitialPassword::class])->group(function(Router $route) {
    $route->prefix('address')->group(function(Router $route) {
        $route->prefix('countries')->group(function(Router $route) {
            $route->get('/', [CountryController::class, 'index'])->name(RouteNames::LIVE_API_V1_ADDRESS_COUNTRY_INDEX);
            $route->get('/{' . RouteParameters::COUNTRY . '}/states', [StateController::class, 'index'])
                ->name(RouteNames::LIVE_API_V1_ADDRESS_COUNTRY_STATE_INDEX);
        });
    });

    $route->prefix('supplier')->group(function(Router $route) {
        $route->get('/', [SupplierController::class, 'show'])->name(RouteNames::LIVE_API_V1_SUPPLIER_SHOW);
        $route->patch('/', [SupplierController::class, 'update'])->name(RouteNames::LIVE_API_V1_SUPPLIER_UPDATE);
        $route->prefix('bulk-hour')->group(function(Router $route) {
            $route->post('/', [BulkHourController::class, 'store'])
                ->name(RouteNames::LIVE_API_V1_SUPPLIER_BULK_HOUR_STORE);
        });
        $route->prefix('bulk-brand')->group(function(Router $route) {
            $route->post('/', [BulkBrandController::class, 'store'])
                ->name(RouteNames::LIVE_API_V1_SUPPLIER_BULK_BRAND_STORE);
        });

        $route->prefix('users')->group(function(Router $route) {
            $route->get('/', [CustomerController::class, 'index'])->name(RouteNames::LIVE_API_V1_SUPPLIER_USER_INDEX);

            $route->prefix('/{' . RouteParameters::CHANNEL . '}')->group(function(Router $route) {
                $route->get('/', [CustomerController::class, 'show'])->name(RouteNames::LIVE_API_V1_SUPPLIER_USER_SHOW);
            });
        });
    });

    $route->prefix('users')->group(function(Router $route) {
        $route->get('/', [UserController::class, 'index'])->name(RouteNames::LIVE_API_V1_USER_INDEX);
        $route->prefix('/{' . RouteParameters::USER . '}')->group(function(Router $route) {
            $route->post('/confirm', [ConfirmedSupplierUserController::class, 'store'])
                ->middleware(['can:updateUnconfirmed,' . RouteParameters::USER])
                ->name(RouteNames::LIVE_API_V1_USER_CONFIRM_USER_STORE);
            $route->post('/new-message', NewMessageController::class)->name(RouteNames::LIVE_API_V1_USER_NEW_MESSAGE);
            $route->get('/orders', [OrderController::class, 'index'])->name(RouteNames::LIVE_API_V1_USER_ORDER_INDEX);
        });
    });

    $route->prefix('confirmed-users')->group(function(Router $route) {
        $route->post('/{' . RouteParameters::USER . '}/confirm', [ConfirmedUserController::class, 'confirm'])
            ->middleware('can:confirm,' . RouteParameters::USER)
            ->name(RouteNames::LIVE_API_V1_CONFIRMED_USER_CONFIRM);
        $route->delete('/{' . RouteParameters::USER . '}/confirm', [ConfirmedUserController::class, 'delete'])
            ->middleware('can:delete,' . RouteParameters::USER)
            ->name(RouteNames::LIVE_API_V1_CONFIRMED_USER_DELETE);
        $route->patch('/{' . RouteParameters::USER . '}', [ConfirmedUserController::class, 'update'])
            ->middleware('can:update,' . RouteParameters::USER)
            ->name(RouteNames::LIVE_API_V1_CONFIRMED_USER_UPDATE);
    });

    $route->prefix('removed-users')->group(function(Router $route) {
        $route->get('/', [RemovedUserController::class, 'index'])->name(RouteNames::LIVE_API_V1_REMOVED_USER_INDEX);
        $route->post('/{' . RouteParameters::USER . '}', [RemovedUserController::class, 'store'])
            ->middleware(['can:remove,' . User::class . ',user'])
            ->name(RouteNames::LIVE_API_V1_REMOVED_USER_STORE);
        $route->delete('/{' . RouteParameters::USER . '}', [RemovedUserController::class, 'delete'])
            ->middleware(['can:restore,' . User::class . ',user'])
            ->name(RouteNames::LIVE_API_V1_REMOVED_USER_DELETE);
    });

    $route->prefix('brands')->group(function(Router $route) {
        $route->get('/', [BrandController::class, 'index'])->name(RouteNames::LIVE_API_V1_BRAND_INDEX);
        $route->prefix('/{' . RouteParameters::BRAND . '}')->group(function(Router $route) {
            $route->prefix('series')->group(function(Router $route) {
                $route->get('/', [SeriesController::class, 'index'])->name(RouteNames::LIVE_API_V1_BRAND_SERIES_INDEX);
                $route->prefix('/{' . RouteParameters::SERIES . ':' . Series::routeKeyName() . '}')->group(function(
                    Router $route
                ) {
                    $route->get('/oems', [BrandSeriesOemController::class, 'index'])
                        ->name(RouteNames::LIVE_API_V1_BRAND_SERIES_OEM_INDEX);
                });
            });
        });
    });

    $route->prefix('orders')->group(function(Router $route) {
        $route->prefix('in-progress')->group(function(Router $route) {
            $route->get('/', [InProgressController::class, 'index'])
                ->name(RouteNames::LIVE_API_V1_ORDER_IN_PROGRESS_INDEX);
            $route->prefix('/{' . RouteParameters::ORDER . '}')->group(function(Router $route) {
                $route->post('/cancel', InProgressCancelController::class)
                    ->middleware('can:cancelInProgress,' . RouteParameters::ORDER)
                    ->name(RouteNames::LIVE_API_V1_ORDER_IN_PROGRESS_CANCEL_STORE);
                $route->prefix('items')->group(function(Router $route) {
                    $route->get('/', [InProgressItemOrderController::class, 'index'])
                        ->name(RouteNames::LIVE_API_V1_ORDER_IN_PROGRESS_ITEM_ORDER_INDEX)
                        ->middleware('can:read,' . RouteParameters::ORDER);
                    $route->prefix('/{' . RouteParameters::ITEM_ORDER . ':' . ItemOrder::routeKeyName() . '}')
                        ->group(function(Router $route) {
                            $route->post('/remove', RemoveController::class)
                                ->middleware('can:removeItemOrderInProgress,' . RouteParameters::ITEM_ORDER)
                                ->name(RouteNames::LIVE_API_V1_ORDER_IN_PROGRESS_ITEM_ORDER_REMOVE_STORE);
                        });
                });
                $route->prefix('delivery')->group(function(Router $route) {
                    $route->patch('/', [InProgressDeliveryController::class, 'update'])
                        ->name(RouteNames::LIVE_API_V1_ORDER_IN_PROGRESS_DELIVERY_UPDATE)
                        ->middleware('can:updateInProgressDelivery,' . RouteParameters::ORDER);
                    $route->prefix('curri')->group(function(Router $route) {
                        $route->post('/confirm', ConfirmController::class)
                            ->middleware('can:confirmCurriOrder,' . RouteParameters::ORDER)
                            ->name(RouteNames::LIVE_API_V1_ORDER_IN_PROGRESS_DELIVERY_CURRI_CONFIRM_STORE);
                        $route->post('/calculate-price', InProgressPriceController::class)
                            ->middleware('can:getCurriDeliveryPrice,' . RouteParameters::ORDER)
                            ->name(RouteNames::LIVE_API_V1_ORDER_IN_PROGRESS_DELIVERY_CURRI_PRICE_STORE);
                        $route->post('notice/en-route/confirm', NoticeEnRouteConfirmController::class)
                            ->middleware('can:confirmNoticeEnRouteCurriDelivery,' . RouteParameters::ORDER)
                            ->name(RouteNames::LIVE_API_V1_ORDER_IN_PROGRESS_DELIVERY_CURRI_NOTICE_ENROUTE_CONFIRM_STORE);
                    });
                });
            });
        });

        $route->prefix('/{' . RouteParameters::ORDER . '}')->group(function(Router $route) {
            $route->post('/assignment', [AssignController::class, 'store'])
                ->name(RouteNames::LIVE_API_V1_ORDER_ASSIGNMENT_STORE)
                ->middleware('can:assign,' . RouteParameters::ORDER);
            $route->post('/pre-approve', PreApprovalController::class)
                ->name(RouteNames::LIVE_API_V1_ORDER_PRE_APPROVAL_STORE)
                ->middleware('can:preApprove,' . RouteParameters::ORDER);
            $route->delete('/assignment', [AssignController::class, 'delete'])
                ->name(RouteNames::LIVE_API_V1_ORDER_ASSIGNMENT_DELETE)
                ->middleware('can:assign,' . RouteParameters::ORDER);
            $route->post('/custom-item', [CustomItemController::class, 'store'])
                ->name(RouteNames::LIVE_API_V1_ORDER_CUSTOM_ITEM_STORE)
                ->middleware('can:update,' . RouteParameters::ORDER)
                ->middleware('can:createCustomItem,' . RouteParameters::ORDER);
            $route->prefix('delivery')->group(function(Router $route) {
                $route->patch('/', [DeliveryController::class, 'update'])
                    ->name(RouteNames::LIVE_API_V1_ORDER_DELIVERY_UPDATE)
                    ->middleware(['can:update,' . RouteParameters::ORDER]);
                $route->patch('/eta', UpdateEtaController::class)
                    ->name(RouteNames::LIVE_API_V1_ORDER_DELIVERY_ETA_UPDATE)
                    ->middleware('can:update,' . RouteParameters::ORDER);
            });
            $route->post('/fees', [FeeController::class, 'store'])
                ->name(RouteNames::LIVE_API_V1_ORDER_FEE_STORE)
                ->middleware(['can:update,' . RouteParameters::ORDER]);
            $route->post('/send-for-approval', SendForApprovalController::class)
                ->name(RouteNames::LIVE_API_V1_ORDER_SEND_FOR_APPROVAL_STORE)
                ->middleware(['can:sendForApproval,' . RouteParameters::ORDER]);
            $route->post('/reopen', [ReopenController::class, 'store'])
                ->name(RouteNames::LIVE_API_V1_ORDER_REOPEN_STORE)
                ->middleware(['can:reopen,' . RouteParameters::ORDER]);
            $route->post('/cancel', CancelController::class)
                ->middleware('can:cancel,' . RouteParameters::ORDER)
                ->name(RouteNames::LIVE_API_V1_ORDER_CANCEL_STORE);
            $route->prefix('items')->group(function(Router $route) {
                $route->get('/', [ItemOrderController::class, 'index'])
                    ->name(RouteNames::LIVE_API_V1_ORDER_ITEM_ORDER_INDEX)
                    ->middleware('can:read,' . RouteParameters::ORDER);
                $route->prefix('/{' . RouteParameters::ITEM_ORDER . ':' . ItemOrder::routeKeyName() . '}')
                    ->group(function(Router $route) {
                        $route->delete('/', [ItemOrderController::class, 'delete'])
                            ->name(RouteNames::LIVE_API_V1_ORDER_ITEM_ORDER_DELETE)
                            ->middleware('can:update,' . RouteParameters::ORDER)
                            ->middleware('can:delete,' . RouteParameters::ITEM_ORDER);
                        $route->get('/', [ItemOrderController::class, 'show'])
                            ->name(RouteNames::LIVE_API_V1_ORDER_ITEM_ORDER_SHOW)
                            ->middleware('can:read,' . RouteParameters::ORDER);
                        $route->patch('/', [ItemOrderController::class, 'update'])
                            ->middleware('can:update,' . RouteParameters::ORDER)
                            ->name(RouteNames::LIVE_API_V1_ORDER_ITEM_ORDER_UPDATE);
                    });
                $route->get('/{' . RouteParameters::PART_ITEM_ORDER . ':' . ItemOrder::routeKeyName() . '}/replacements',
                    [ItemOrderReplacementController::class, 'index'])
                    ->name(RouteNames::LIVE_API_V1_ORDER_ITEM_ORDER_REPLACEMENT_INDEX)
                    ->middleware('can:read,' . RouteParameters::ORDER);
            });
            $route->post('/complete', CompleteController::class)
                ->middleware('can:complete,' . RouteParameters::ORDER)
                ->name(RouteNames::LIVE_API_V1_ORDER_COMPLETE_STORE);
        });
    });

    $route->prefix('oems')->group(function(Router $route) {
        $route->get('/', [OemController::class, 'index'])->name(RouteNames::LIVE_API_V1_OEM_INDEX);
        $route->prefix('/{' . RouteParameters::OEM . '}')->group(function(Router $route) {
            $route->get('/', [OemController::class, 'show'])->name(RouteNames::LIVE_API_V1_OEM_SHOW);
            $route->prefix('parts')->group(function(Router $route) {
                $route->get('/', [OemPartController::class, 'index'])->name(RouteNames::LIVE_API_V1_OEM_PART_INDEX);;
            });
        });
    });

    $route->prefix('parts')->group(function(Router $route) {
        $route->get('/', [PartController::class, 'index'])->name(RouteNames::LIVE_API_V1_PART_INDEX);
        $route->prefix('/{' . RouteParameters::PART . '}')->group(function(Router $route) {
            $route->get('/', [PartController::class, 'show'])->name(RouteNames::LIVE_API_V1_PART_SHOW);
            $route->post('/recommended-replacements', [RecommendedReplacementController::class, 'store'])
                ->name(RouteNames::LIVE_API_V1_PART_RECOMMENDED_REPLACEMENT_STORE);
            $route->get('/replacements', [ReplacementController::class, 'index'])
                ->name(RouteNames::LIVE_API_V1_PART_REPLACEMENT_INDEX);
        });
    });

    $route->prefix('app-settings')->group(function(Router $route) {
        $route->prefix('/{' . RouteParameters::APP_SETTING . '}')->group(function(Router $route) {
            $route->get('/', [AppSettingController::class, 'show'])->name(RouteNames::LIVE_API_V1_APP_SETTING_SHOW);
        });
    });

    /** @deprecated */
    $route->prefix('notification-settings')->group(function(Router $route) {
        $route->get('/', [NotificationSettingController::class, 'index'])
            ->name(RouteNames::LIVE_API_V1_NOTIFICATION_SETTING_INDEX);
        $route->post('/', [NotificationSettingController::class, 'store'])
            ->name(RouteNames::LIVE_API_V1_NOTIFICATION_SETTING_STORE);
    });

    $route->prefix('settings')->group(function(Router $route) {
        $route->get('/', [SettingController::class, 'index'])->name(RouteNames::LIVE_API_V1_SETTING_INDEX);

        $route->prefix('bulk-notification')->group(function(Router $route) {
            $route->post('/', [BulkNotificationController::class, 'store'])
                ->name(RouteNames::LIVE_API_V1_SETTING_BULK_NOTIFICATION_STORE);
        });
        $route->prefix('/{' . RouteParameters::SETTING_SUPPLIER . '}')->group(function(Router $route) {
            $route->get('/', [SettingController::class, 'show'])->name(RouteNames::LIVE_API_V1_SETTING_SHOW);
        });
    });
});

Route::prefix('unauthenticated')->group(function(Router $route) {
    $route->prefix('orders')->group(function(Router $route) {
        $route->prefix('/{' . RouteParameters::UNAUTHENTICATED_ORDER . '}')->group(function(Router $route) {
            $route->get('/', [UnauthenticatedOrderController::class, 'show'])
                ->name(RouteNames::LIVE_API_V1_UNAUTHENTICATED_ORDER_SHOW);
            $route->post('/approve', UnauthenticatedApproveController::class)
                ->middleware('can:approveUnauthenticated,' . RouteParameters::UNAUTHENTICATED_ORDER)
                ->name(RouteNames::LIVE_API_V1_UNAUTHENTICATED_ORDER_APPROVE_STORE);
            $route->prefix('items')->group(function(Router $route) {
                $route->get('/', [UnauthenticatedItemOrderController::class, 'index'])
                    ->name(RouteNames::LIVE_API_V1_UNAUTHENTICATED_ORDER_ITEM_ORDER_INDEX);
            });
        });
    });
});
