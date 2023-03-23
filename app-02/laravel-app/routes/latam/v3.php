<?php /** @noinspection DuplicatedCode */

use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Http\Controllers\Api\V2\Post\Comment\VoteController;
use App\Http\Controllers\Api\V2\Post\CommentController as CommentControllerV2;
use App\Http\Controllers\Api\V2\Post\SolutionController;
use App\Http\Controllers\Api\V2\PostController as PostControllerV2;
use App\Http\Controllers\Api\V2\PushNotificationTokenController;
use App\Http\Controllers\Api\V2\Support\Ticket\AgentHistoryController;
use App\Http\Controllers\Api\V2\Support\Ticket\AgentRateController;
use App\Http\Controllers\Api\V2\Support\Ticket\CloseController;
use App\Http\Controllers\Api\V2\Support\Ticket\RateController;
use App\Http\Controllers\Api\V2\Taggable\FollowController;
use App\Http\Controllers\Api\V2\TaggableController;
use App\Http\Controllers\Api\V2\Twilio\TokenController;
use App\Http\Controllers\Api\V2\Twilio\Webhook\Call\ActionController;
use App\Http\Controllers\Api\V2\Twilio\Webhook\Call\Client\StatusController;
use App\Http\Controllers\Api\V2\Twilio\Webhook\Call\CompleteController as TwilioWebhookCallCompleteController;
use App\Http\Controllers\Api\V2\Twilio\Webhook\Call\FallbackController as TwilioWebhookCallFallbackController;
use App\Http\Controllers\Api\V2\Twilio\Webhook\CallController as TwilioWebhookCallController;
use App\Http\Controllers\Api\V2\User\FollowedPostController;
use App\Http\Controllers\Api\V2\User\SettingController;
use App\Http\Controllers\Api\V3\Account\BriefSupplierController;
use App\Http\Controllers\Api\V3\Account\BulkFavoriteSeriesController;
use App\Http\Controllers\Api\V3\Account\BulkSupplierController;
use App\Http\Controllers\Api\V3\Account\Cart\CartItemController;
use App\Http\Controllers\Api\V3\Account\CartController;
use App\Http\Controllers\Api\V3\Account\GroupedSupplierController;
use App\Http\Controllers\Api\V3\Account\Oem\RecentlyViewedController as OemRecentlyViewedController;
use App\Http\Controllers\Api\V3\Account\OemController as AccountOemController;
use App\Http\Controllers\Api\V3\Account\Part\RecentlyViewedController as PartRecentlyViewedController;
use App\Http\Controllers\Api\V3\Account\Phone\CallController as AccountPhoneCallController;
use App\Http\Controllers\Api\V3\Account\Phone\SmsController as AccountPhoneSmsController;
use App\Http\Controllers\Api\V3\Account\Phone\VerifyController as AccountPhoneVerifyController;
use App\Http\Controllers\Api\V3\Account\Point\RedemptionController;
use App\Http\Controllers\Api\V3\Account\Point\XoxoVoucher\RedeemController;
use App\Http\Controllers\Api\V3\Account\PointController;
use App\Http\Controllers\Api\V3\Account\ProfileController;
use App\Http\Controllers\Api\V3\Account\PubnubChannelController;
use App\Http\Controllers\Api\V3\Account\RecentlyViewedController;
use App\Http\Controllers\Api\V3\Account\Supplier\ChannelController;
use App\Http\Controllers\Api\V3\Account\SupplierController as AccountSupplierController;
use App\Http\Controllers\Api\V3\Account\Supply\RecentlyAddedController;
use App\Http\Controllers\Api\V3\Account\Term\AcceptController;
use App\Http\Controllers\Api\V3\Account\VerifiedSupplierController;
use App\Http\Controllers\Api\V3\Account\Wishlist\ItemWishlistController;
use App\Http\Controllers\Api\V3\Account\WishlistController;
use App\Http\Controllers\Api\V3\AccountController;
use App\Http\Controllers\Api\V3\ActivityController;
use App\Http\Controllers\Api\V3\Address\Country\StateController;
use App\Http\Controllers\Api\V3\Address\CountryController;
use App\Http\Controllers\Api\V3\AppSettingController;
use App\Http\Controllers\Api\V3\AppVersion\ConfirmController;
use App\Http\Controllers\Api\V3\AppVersionController;
use App\Http\Controllers\Api\V3\Auth\CreatePasswordController;
use App\Http\Controllers\Api\V3\Auth\Email\LoginController;
use App\Http\Controllers\Api\V3\Auth\LogoutController;
use App\Http\Controllers\Api\V3\Auth\Phone\Login\CallController as AuthPhoneLoginCallController;
use App\Http\Controllers\Api\V3\Auth\Phone\Login\SmsController as AuthPhoneLoginSmsController;
use App\Http\Controllers\Api\V3\Auth\Phone\Login\VerifyController as AuthPhoneLoginVerifyController;
use App\Http\Controllers\Api\V3\Auth\Phone\Register\AssignController;
use App\Http\Controllers\Api\V3\Auth\Phone\Register\CallController as AuthPhoneRegisterCallController;
use App\Http\Controllers\Api\V3\Auth\Phone\Register\SmsController as AuthPhoneRegisterSmsController;
use App\Http\Controllers\Api\V3\Auth\Phone\Register\VerifyController as AuthPhoneRegisterVerifyController;
use App\Http\Controllers\Api\V3\Auth\RefreshController;
use App\Http\Controllers\Api\V3\Brand\MostSearchedController;
use App\Http\Controllers\Api\V3\Brand\Series\OemController as BrandSeriesOemController;
use App\Http\Controllers\Api\V3\Brand\SeriesController as BrandSeriesController;
use App\Http\Controllers\Api\V3\BrandController;
use App\Http\Controllers\Api\V3\CustomItemController;
use App\Http\Controllers\Api\V3\InternalNotification\MarkAsReadController;
use App\Http\Controllers\Api\V3\InternalNotificationController;
use App\Http\Controllers\Api\V3\ModelType\Brand\Series\OemController as ModelTypeBrandSeriesOemController;
use App\Http\Controllers\Api\V3\ModelType\Brand\SeriesController as ModelTypeBrandSeriesController;
use App\Http\Controllers\Api\V3\ModelType\BrandController as ModelTypeBrandController;
use App\Http\Controllers\Api\V3\ModelTypeController;
use App\Http\Controllers\Api\V3\NoteController;
use App\Http\Controllers\Api\V3\Oem\PartController as OemPartController;
use App\Http\Controllers\Api\V3\OemController;
use App\Http\Controllers\Api\V3\Order\ApproveController;
use App\Http\Controllers\Api\V3\Order\CancelController;
use App\Http\Controllers\Api\V3\Order\Delivery\Curri\ConfirmController as CurriConfirmController;
use App\Http\Controllers\Api\V3\Order\Delivery\CurriController;
use App\Http\Controllers\Api\V3\Order\DeliveryController;
use App\Http\Controllers\Api\V3\Order\ItemOrderController;
use App\Http\Controllers\Api\V3\Order\ShareController;
use App\Http\Controllers\Api\V3\OrderController;
use App\Http\Controllers\Api\V3\OrderSupplierController;
use App\Http\Controllers\Api\V3\PartController;
use App\Http\Controllers\Api\V3\Point\XoxoVoucherController;
use App\Http\Controllers\Api\V3\Post\CommentController;
use App\Http\Controllers\Api\V3\Post\PinController;
use App\Http\Controllers\Api\V3\Post\UserController as PostUserController;
use App\Http\Controllers\Api\V3\Post\VoteController as PostVoteController;
use App\Http\Controllers\Api\V3\PostController;
use App\Http\Controllers\Api\V3\Supplier\ChangeRequestController;
use App\Http\Controllers\Api\V3\Supplier\InviteController;
use App\Http\Controllers\Api\V3\Supplier\NewMessageController;
use App\Http\Controllers\Api\V3\SupplierController;
use App\Http\Controllers\Api\V3\Supply\SearchController as SupplySearchController;
use App\Http\Controllers\Api\V3\SupplyCategory\SupplySubcategoryController;
use App\Http\Controllers\Api\V3\SupplyCategoryController;
use App\Http\Controllers\Api\V3\SupplyController;
use App\Http\Controllers\Api\V3\SupportCallCategory\SupportCallSubcategoryController;
use App\Http\Controllers\Api\V3\SupportCallCategoryController;
use App\Http\Controllers\Api\V3\SupportCallController;
use App\Http\Controllers\Api\V3\TechniciansController;
use App\Http\Controllers\Api\V3\Twilio\Webhooks\Auth\Call\CompleteController as TwilioWebhooksAuthCallCompleteController;
use App\Http\Controllers\Api\V3\Twilio\Webhooks\Auth\Call\FallbackController as TwilioWebhooksAuthCallFallbackController;
use App\Http\Controllers\Api\V3\Twilio\Webhooks\Auth\CallController as TwilioWebhooksAuthCallController;
use App\Http\Controllers\Api\V3\User\CountController;
use App\Http\Controllers\Api\V3\User\PostController as UserPostController;
use App\Http\Controllers\Api\V3\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Middleware\AuthenticatePhone;
use App\Http\Middleware\AuthenticateUser;
use App\Http\Middleware\DenyAgentGroupSettingIfNoAgentAuthenticated;
use App\Http\Middleware\ProvideLatamPhone;
use App\Http\Middleware\ValidateIfPhoneCanMakeSMSRequests;
use App\Http\Middleware\ValidateMaximumWishlistItems;
use App\Http\Middleware\ValidateMaximumWishlists;
use App\Http\Middleware\ValidatePointsOnSupportCall;
use App\Http\Middleware\ValidateSupplierInvitation;
use App\Http\Middleware\ValidateTwilioRequest;
use App\Models\Comment;
use App\Models\ItemWishlist;
use App\Models\Note;
use Illuminate\Routing\Router;

/* |--- FALLBACK TO V2 ---| */
Route::middleware(AuthenticateUser::class)->group(function(Router $route) {
    $route->prefix('activity')->group(function(Router $route) {
        $route->get('/', [ActivityController::class, 'index'])->name(RouteNames::API_V3_ACTIVITY_INDEX);
    });

    $route->prefix('internal-notifications')->group(function(Router $route) {
        $route->get('/', [InternalNotificationController::class, 'index'])
            ->name(RouteNames::API_V3_INTERNAL_NOTIFICATION_INDEX);
        $route->patch('mark-as-read', MarkAsReadController::class)
            ->name(RouteNames::API_V3_INTERNAL_NOTIFICATION_MARK_AS_READ);
        $route->prefix('{' . RouteParameters::INTERNAL_NOTIFICATION . '}')->group(function(Router $route) {
            $route->get('/', [InternalNotificationController::class, 'show'])
                ->name(RouteNames::API_V3_INTERNAL_NOTIFICATION_SHOW)
                ->middleware('can:read,' . RouteParameters::INTERNAL_NOTIFICATION);
        });
    });

    $route->prefix('posts')->group(function(Router $route) {
        $route->prefix('{' . RouteParameters::POST . '}')->group(function(Router $route) {
            $route->get('/', [PostControllerV2::class, 'show'])->name(RouteNames::API_V3_POST_SHOW);
            $route->delete('/', [PostControllerV2::class, 'delete'])
                ->name(RouteNames::API_V3_POST_DELETE)
                ->middleware('can:delete,' . RouteParameters::POST);

            $route->prefix('comments')->group(function(Router $route) {
                $route->get('/', [CommentControllerV2::class, 'index'])->name(RouteNames::API_V3_POST_COMMENT_INDEX);

                $route->prefix('{' . RouteParameters::COMMENT . ':' . Comment::routeKeyName() . '}')->group(function(
                    Router $route
                ) {
                    $route->delete('/', [CommentControllerV2::class, 'delete'])
                        ->name(RouteNames::API_V3_POST_COMMENT_DELETE)
                        ->middleware('can:delete,' . RouteParameters::COMMENT);

                    $route->prefix('vote')->group(function(Router $route) {
                        $route->post('/', [VoteController::class, 'store'])
                            ->name(RouteNames::API_V3_POST_COMMENT_VOTE_STORE);
                        $route->delete('/', [VoteController::class, 'delete'])
                            ->name(RouteNames::API_V3_POST_COMMENT_VOTE_DELETE);
                    });
                });
            });

            $route->prefix('solution')->group(function(Router $route) {
                $route->post('/', [SolutionController::class, 'store'])
                    ->name(RouteNames::API_V3_POST_SOLUTION_STORE)
                    ->middleware('can:solve,' . RouteParameters::POST);

                $route->prefix('{' . RouteParameters::COMMENT . ':' . Comment::routeKeyName() . '}')->group(function(
                    Router $route
                ) {
                    $route->delete('/', [SolutionController::class, 'delete'])
                        ->name(RouteNames::API_V3_POST_SOLUTION_DELETE)
                        ->middleware('can:unSolve,' . RouteParameters::POST);
                });
            });
        });
    });

    $route->prefix('products')->group(function(Router $route) {
        $route->prefix('{' . RouteParameters::PRODUCT . '}')->group(function(Router $route) {
            $route->get('/', [ProductController::class, 'info'])->name(RouteNames::API_V3_PRODUCT_INFO);
        });
    });

    $route->prefix('push-notification-token')->group(function(Router $route) {
        $route->post('/', [PushNotificationTokenController::class, 'store'])
            ->name(RouteNames::API_V3_PUSH_NOTIFICATION_TOKEN_STORE);
    });

    $route->prefix('tags')->group(function(Router $route) {
        $route->get('/', [TaggableController::class, 'index'])->name(RouteNames::API_V3_TAGGABLE_INDEX);

        $route->prefix('{' . RouteParameters::TAGGABLE . '}')->group(function(Router $route) {
            $route->get('/', [TaggableController::class, 'show'])->name(RouteNames::API_V3_TAGGABLE_SHOW);

            $route->prefix('follow')->group(function(Router $route) {
                $route->post('/', [FollowController::class, 'store'])->name(RouteNames::API_V3_TAGGABLE_FOLLOW_STORE);
                $route->delete('/', [FollowController::class, 'delete'])
                    ->name(RouteNames::API_V3_TAGGABLE_FOLLOW_DELETE);
            });
        });
    });

    $route->prefix('twilio')->group(function(Router $route) {
        $route->prefix('token')->group(function(Router $route) {
            $route->post('/', [TokenController::class, 'store'])->name(RouteNames::API_V3_TWILIO_TOKEN_STORE);
        });
    });

    $route->prefix('support')->group(function(Router $route) {
        $route->prefix('ticket')->group(function(Router $route) {
            $route->prefix('{' . RouteParameters::TICKET . '}')->group(function(Router $route) {
                $route->prefix('agent-rate')->group(function(Router $route) {
                    $route->post('/', [AgentRateController::class, 'store'])
                        ->name(RouteNames::API_V3_SUPPORT_TICKET_AGENT_RATE_STORE)
                        ->middleware('can:agentRate,' . RouteParameters::TICKET);
                });

                $route->prefix('close')->group(function(Router $route) {
                    $route->post('/', [CloseController::class, 'store'])
                        ->name(RouteNames::API_V3_SUPPORT_TICKET_CLOSE_STORE)
                        ->middleware('can:close,' . RouteParameters::TICKET);
                });

                $route->prefix('rate')->group(function(Router $route) {
                    $route->post('/', [RateController::class, 'store'])
                        ->name(RouteNames::API_V3_SUPPORT_TICKET_RATE_STORE)
                        ->middleware('can:rate,' . RouteParameters::TICKET);
                });
            });

            $route->prefix('agent-history')->group(function(Router $route) {
                $route->get('/', [AgentHistoryController::class, 'index'])
                    ->name(RouteNames::API_V3_SUPPORT_TICKET_AGENT_HISTORY_INDEX)
                    ->middleware('can:seeAgentHistory,' . RouteParameters::TICKET);
            });
        });

        $route->prefix('technicians')->group(function(Router $route) {
            $route->get('/', [TechniciansController::class, 'show'])->name(RouteNames::API_V3_SUPPORT_TECHNICIAN_SHOW);
        });
    });

    $route->prefix('user')->group(function(Router $route) {
        $route->prefix('followed-posts')->group(function(Router $route) {
            $route->get('/', [FollowedPostController::class, 'index'])
                ->name(RouteNames::API_V3_USER_FOLLOWED_POST_INDEX);
        });

        $route->prefix('settings')->group(function(Router $route) {
            $route->prefix('{' . RouteParameters::SETTING_USER . '}')->group(function(Router $route) {
                $route->patch('/', [SettingController::class, 'update'])
                    ->name(RouteNames::API_V3_USER_SETTING_UPDATE)
                    ->middleware(DenyAgentGroupSettingIfNoAgentAuthenticated::class);
            });
        });
    });
});

Route::prefix('twilio')->middleware(ValidateTwilioRequest::class)->group(function(Router $route) {
    $route->prefix('webhook')->group(function(Router $route) {
        $route->prefix('call')->group(function(Router $route) {
            $route->post('/', [TwilioWebhookCallController::class, 'store'])
                ->name(RouteNames::API_V3_TWILIO_WEBHOOK_CALL_STORE);

            $route->prefix('action')->group(function(Router $route) {
                $route->post('/', [ActionController::class, 'store'])
                    ->name(RouteNames::API_V3_TWILIO_WEBHOOK_CALL_ACTION_STORE);
            });

            $route->prefix('client')->group(function(Router $route) {
                $route->prefix('status')->group(function(Router $route) {
                    $route->post('/', [StatusController::class, 'store'])
                        ->name(RouteNames::API_V3_TWILIO_WEBHOOK_CALL_CLIENT_STATUS_STORE);
                });
            });

            $route->prefix('complete')->group(function(Router $route) {
                $route->post('/', [TwilioWebhookCallCompleteController::class, 'store'])
                    ->name(RouteNames::API_V3_TWILIO_WEBHOOK_CALL_COMPLETE_STORE);
            });

            $route->prefix('fallback')->group(function(Router $route) {
                $route->post('/', [TwilioWebhookCallFallbackController::class, 'store'])
                    ->name(RouteNames::API_V3_TWILIO_WEBHOOK_CALL_FALLBACK_STORE);
            });
        });
    });
});

/* |--- V3 ---| */
Route::prefix('users')->group(function(Router $route) {
    $route->get('count', CountController::class)->name(RouteNames::API_V3_USER_COUNT);
});

Route::middleware(AuthenticateUser::class)->group(function(Router $route) {
    $route->prefix('account')->group(function(Router $route) {
        $route->get('/', [AccountController::class, 'show'])->name(RouteNames::API_V3_ACCOUNT_SHOW);
        $route->delete('/', [AccountController::class, 'delete'])->name(RouteNames::API_V3_ACCOUNT_DELETE);

        $route->get('brief-suppliers', BriefSupplierController::class)
            ->name(RouteNames::API_V3_ACCOUNT_BRIEF_SUPPLIER_INDEX);

        $route->get('grouped-suppliers', [GroupedSupplierController::class, 'index'])
            ->name(RouteNames::API_V3_ACCOUNT_GROUPED_SUPPLIER_INDEX);

        $route->post('bulk-favorite-series', BulkFavoriteSeriesController::class)
            ->name(RouteNames::API_V3_ACCOUNT_BULK_FAVORITE_SERIES_STORE);
        $route->post('bulk-suppliers', BulkSupplierController::class)
            ->name(RouteNames::API_V3_ACCOUNT_BULK_SUPPLIER_STORE);

        $route->prefix('channels')->group(function(Router $route) {
            $route->get('/', [PubnubChannelController::class, 'index'])->name(RouteNames::API_V3_ACCOUNT_CHANNEL_INDEX);
        });

        $route->prefix('cart')->group(function(Router $route) {
            $route->get('/', [CartController::class, 'show'])->name(RouteNames::API_V3_ACCOUNT_CART_SHOW);
            $route->post('/', [CartController::class, 'store'])->name(RouteNames::API_V3_ACCOUNT_CART_STORE);
            $route->delete('/', [CartController::class, 'delete'])->name(RouteNames::API_V3_ACCOUNT_CART_DELETE);

            $route->prefix('items')->group(function(Router $route) {
                $route->get('/', [CartItemController::class, 'index'])
                    ->name(RouteNames::API_V3_ACCOUNT_CART_ITEM_INDEX);
                $route->post('/', [CartItemController::class, 'store'])
                    ->name(RouteNames::API_V3_ACCOUNT_CART_ITEM_STORE);
                $route->prefix('{' . RouteParameters::CART_ITEM . '}')->group(function(Router $route) {
                    $route->patch('/', [CartItemController::class, 'update'])
                        ->name(RouteNames::API_V3_ACCOUNT_CART_ITEM_UPDATE)
                        ->middleware('can:update,' . RouteParameters::CART_ITEM);
                    $route->delete('/', [CartItemController::class, 'delete'])
                        ->name(RouteNames::API_V3_ACCOUNT_CART_ITEM_DELETE)
                        ->middleware('can:delete,' . RouteParameters::CART_ITEM);
                });
            });
        });

        $route->prefix('phones')->group(function(Router $route) {
            $route->prefix('{' . RouteParameters::UNVERIFIED_PHONE . '}')->group(function(Router $route) {
                $route->post('verify', AccountPhoneVerifyController::class)
                    ->name(RouteNames::API_V3_ACCOUNT_PHONE_VERIFY);
            });

            $route->post('call', AccountPhoneCallController::class)->name(RouteNames::API_V3_ACCOUNT_PHONE_CALL);
            $route->post('sms', AccountPhoneSmsController::class)->name(RouteNames::API_V3_ACCOUNT_PHONE_SMS);
        });

        $route->prefix('points')->group(function(Router $route) {
            $route->get('/', PointController::class)->name(RouteNames::API_V3_ACCOUNT_POINT_SHOW);
            $route->get('redemptions', [RedemptionController::class, 'index'])
                ->name(RouteNames::API_V3_ACCOUNT_POINT_REDEMPTION_INDEX);

            $route->prefix('vouchers')->group(function(Router $route) {
                $route->prefix('/{' . RouteParameters::VOUCHER . '}')->group(function(Router $route) {
                    $route->prefix('redeem')->group(function(Router $route) {
                        $route->post('/', [RedeemController::class, 'store'])
                            ->name(RouteNames::API_V3_ACCOUNT_POINTS_VOUCHERS_REDEEM_STORE);
                    });
                });
            });
        });

        $route->prefix('profile')->group(function(Router $route) {
            $route->get('/', [ProfileController::class, 'show'])->name(RouteNames::API_V3_ACCOUNT_PROFILE_SHOW);
            $route->patch('/', [ProfileController::class, 'update'])->name(RouteNames::API_V3_ACCOUNT_PROFILE_UPDATE);
        });

        $route->prefix('suppliers')->group(function(Router $route) {
            $route->get('/', [AccountSupplierController::class, 'index'])
                ->name(RouteNames::API_V3_ACCOUNT_SUPPLIER_INDEX);
            $route->post('/', [AccountSupplierController::class, 'store'])
                ->name(RouteNames::API_V3_ACCOUNT_SUPPLIER_STORE);

            $route->prefix('channels')->group(function(Router $route) {
                $route->get('/', [ChannelController::class, 'index'])
                    ->name(RouteNames::API_V3_ACCOUNT_SUPPLIER_CHANNEL_INDEX);
            });
        });

        $route->prefix('oems')->group(function(Router $route) {
            $route->get('/', [AccountOemController::class, 'index'])->name(RouteNames::API_V3_ACCOUNT_OEM_INDEX);
            $route->post('/', [AccountOemController::class, 'store'])->name(RouteNames::API_V3_ACCOUNT_OEM_STORE);
            $route->delete('/{' . RouteParameters::OEM . '}', [AccountOemController::class, 'delete'])
                ->name(RouteNames::API_V3_ACCOUNT_OEM_DELETE);
            $route->prefix('recently-viewed')->group(function(Router $route) {
                $route->get('/', OemRecentlyViewedController::class)
                    ->name(RouteNames::API_V3_ACCOUNT_OEM_RECENTLY_VIEWED_INDEX);
            });
        });

        $route->prefix('parts')->group(function(Router $route) {
            $route->prefix('recently-viewed')->group(function(Router $route) {
                $route->get('/', PartRecentlyViewedController::class)
                    ->name(RouteNames::API_V3_ACCOUNT_PART_RECENTLY_VIEWED_INDEX);
            });
        });

        $route->get('recently-viewed', RecentlyViewedController::class)
            ->name(RouteNames::API_V3_ACCOUNT_RECENTLY_VIEWED);

        $route->prefix('supplies')->group(function(Router $route) {
            $route->prefix('recently-added')->group(function(Router $route) {
                $route->get('/', RecentlyAddedController::class)
                    ->name(RouteNames::API_V3_ACCOUNT_SUPPLY_RECENTLY_ADDED_INDEX);
            });
        });
        $route->prefix('terms')->group(function(Router $route) {
            $route->post('accept', AcceptController::class)->name(RouteNames::API_V3_ACCOUNT_TERM_ACCEPT);
        });

        $route->get('verified-suppliers', VerifiedSupplierController::class)
            ->name(RouteNames::API_V3_ACCOUNT_VERIFIED_SUPPLIER_COUNT);

        $route->prefix('wishlists')->group(function(Router $route) {
            $route->get('/', [WishlistController::class, 'index'])->name(RouteNames::API_V3_ACCOUNT_WISHLIST_INDEX);
            $route->post('/', [WishlistController::class, 'store'])
                ->name(RouteNames::API_V3_ACCOUNT_WISHLIST_STORE)
                ->middleware(ValidateMaximumWishlists::class);
            $route->prefix('/{' . RouteParameters::WISHLIST . '}')->group(function(Router $route) {
                $route->patch('/', [WishlistController::class, 'update'])
                    ->name(RouteNames::API_V3_ACCOUNT_WISHLIST_UPDATE)
                    ->middleware('can:read,' . RouteParameters::WISHLIST);
                $route->delete('/', [WishlistController::class, 'delete'])
                    ->name(RouteNames::API_V3_ACCOUNT_WISHLIST_DELETE)
                    ->middleware('can:read,' . RouteParameters::WISHLIST);
                $route->prefix('items')->group(function(Router $route) {
                    $route->get('/', [ItemWishlistController::class, 'index'])
                        ->name(RouteNames::API_V3_ACCOUNT_WISHLIST_ITEM_INDEX)
                        ->middleware('can:read,' . RouteParameters::WISHLIST);
                    $route->post('/', [ItemWishlistController::class, 'store'])
                        ->name(RouteNames::API_V3_ACCOUNT_WISHLIST_ITEM_STORE)
                        ->middleware('can:read,' . RouteParameters::WISHLIST, ValidateMaximumWishlistItems::class);
                    $route->prefix('{' . RouteParameters::ITEM_WISHLIST . ':' . ItemWishlist::routeKeyName() . '}')
                        ->group(function(Router $route) {
                            $route->patch('/', [ItemWishlistController::class, 'update'])
                                ->name(RouteNames::API_V3_ACCOUNT_WISHLIST_ITEM_UPDATE)
                                ->middleware('can:update,' . RouteParameters::ITEM_WISHLIST);
                            $route->delete('/', [ItemWishlistController::class, 'delete'])
                                ->name(RouteNames::API_V3_ACCOUNT_WISHLIST_ITEM_DELETE)
                                ->middleware('can:delete,' . RouteParameters::ITEM_WISHLIST);
                        });
                });
            });
        });
    });

    $route->prefix('address')->group(function(Router $route) {
        $route->prefix('countries')->group(function(Router $route) {
            $route->get('/', [CountryController::class, 'index'])->name(RouteNames::API_V3_ADDRESS_COUNTRY_INDEX);

            $route->prefix('{' . RouteParameters::COUNTRY . '}')->group(function(Router $route) {
                $route->prefix('states')->group(function(Router $route) {
                    $route->get('/', [StateController::class, 'index'])
                        ->name(RouteNames::API_V3_ADDRESS_COUNTRY_STATE_INDEX);
                });
            });
        });
    });

    $route->prefix('app-settings')->group(function(Router $route) {
        $route->prefix('{' . RouteParameters::APP_SETTING . '}')->group(function(Router $route) {
            $route->get('/', [AppSettingController::class, 'show'])->name(RouteNames::API_V3_APP_SETTING_SHOW);
        });
    });

    $route->prefix('app-version')->group(function(Router $route) {
        $route->post('confirm', ConfirmController::class)->name(RouteNames::API_V3_APP_VERSION_CONFIRM);
    });

    $route->prefix('brands')->group(function(Router $route) {
        $route->get('/', [BrandController::class, 'index'])->name(RouteNames::API_V3_BRAND_INDEX);
        $route->get('most-searched', MostSearchedController::class)->name(RouteNames::API_V3_BRAND_MOST_SEARCHED_INDEX);

        $route->prefix('/{' . RouteParameters::BRAND . '}')->group(function(Router $route) {
            $route->prefix('series')->group(function(Router $route) {
                $route->get('/', [BrandSeriesController::class, 'index'])->name(RouteNames::API_V3_BRAND_SERIES_INDEX);

                $route->prefix('/{' . RouteParameters::SERIES . ':' . App\Models\Series::routeKeyName() . '}')
                    ->group(function(Router $route) {
                        $route->prefix('oems')->group(function(Router $route) {
                            $route->get('/', [BrandSeriesOemController::class, 'index'])
                                ->name(RouteNames::API_V3_BRAND_SERIES_OEM_INDEX);
                        });
                    });
            });
        });
    });

    $route->post('custom-items', CustomItemController::class)->name(RouteNames::API_V3_CUSTOM_ITEM_STORE);

    $route->prefix('model-types')->group(function(Router $route) {
        $route->get('/', [ModelTypeController::class, 'index'])->name(RouteNames::API_V3_MODEL_TYPE_INDEX);

        $route->prefix('/{' . RouteParameters::MODEL_TYPE . '}')->group(function(Router $route) {
            $route->prefix('brands')->group(function(Router $route) {
                $route->get('/', [ModelTypeBrandController::class, 'index'])
                    ->name(RouteNames::API_V3_MODEL_TYPE_BRAND_INDEX);

                $route->prefix('/{' . RouteParameters::BRAND . '}')->group(function(Router $route) {
                    $route->prefix('series')->group(function(Router $route) {
                        $route->get('/', [ModelTypeBrandSeriesController::class, 'index'])
                            ->name(RouteNames::API_V3_MODEL_TYPE_BRAND_SERIES_INDEX);

                        $route->prefix('/{' . RouteParameters::SERIES . ':' . App\Models\Series::routeKeyName() . '}')
                            ->group(function(Router $route) {
                                $route->prefix('oems')->group(function(Router $route) {
                                    $route->get('/', [ModelTypeBrandSeriesOemController::class, 'index'])
                                        ->name(RouteNames::API_V3_MODEL_TYPE_BRAND_SERIES_OEM_INDEX);
                                });
                            });
                    });
                });
            });
        });
    });

    $route->prefix('note-categories')->group(function(Router $route) {
        $route->prefix('/{' . RouteParameters::NOTE_CATEGORY . '}')->group(function(Router $route) {
            $route->prefix('notes')->group(function(Router $route) {
                $route->get('/', [NoteController::class, 'index'])->name(RouteNames::API_V3_NOTE_INDEX);
                $route->prefix('/{' . RouteParameters::NOTE . ':' . Note::routeKeyName() . '}')->group(function(
                    Router $route
                ) {
                    $route->get('/', [NoteController::class, 'show'])->name(RouteNames::API_V3_NOTE_SHOW);
                });
            });
        });
    });

    $route->prefix('oems')->group(function(Router $route) {
        $route->get('/', [OemController::class, 'index'])->name(RouteNames::API_V3_OEM_INDEX);

        $route->prefix('/{' . RouteParameters::OEM . '}')->group(function(Router $route) {
            $route->get('/', [OemController::class, 'show'])->name(RouteNames::API_V3_OEM_SHOW);
            $route->get('parts', [OemPartController::class, 'index'])->name(RouteNames::API_V3_OEM_PART_INDEX);
        });
    });

    $route->prefix('orders')->group(function(Router $route) {
        $route->get('/', [OrderController::class, 'index'])->name(RouteNames::API_V3_ORDER_INDEX);
        $route->post('/', [OrderController::class, 'store'])->name(RouteNames::API_V3_ORDER_STORE);
        $route->prefix('/{' . RouteParameters::ORDER . '}')->group(function(Router $route) {
            $route->get('/', [OrderController::class, 'show'])
                ->name(RouteNames::API_V3_ORDER_SHOW)
                ->middleware('can:read,' . RouteParameters::ORDER);
            $route->post('approve', ApproveController::class)
                ->name(RouteNames::API_V3_ORDER_APPROVE_STORE)
                ->middleware('can:approve,' . RouteParameters::ORDER);
            $route->post('cancel', CancelController::class)
                ->name(RouteNames::API_V3_ORDER_CANCEL_STORE)
                ->middleware('can:cancel,' . RouteParameters::ORDER);
            $route->post('share', ShareController::class)
                ->name(RouteNames::API_V3_ORDER_SHARE_STORE)
                ->middleware('can:share,' . RouteParameters::ORDER);

            $route->prefix('items')->group(function(Router $route) {
                $route->get('/', [ItemOrderController::class, 'index'])
                    ->name(RouteNames::API_V3_ORDER_ITEM_ORDER_INDEX)
                    ->middleware('can:read,' . RouteParameters::ORDER);
            });

            $route->prefix('delivery')->group(function(Router $route) {
                $route->patch('/', [DeliveryController::class, 'update'])
                    ->name(RouteNames::API_V3_ORDER_DELIVERY_UPDATE)
                    ->middleware('can:updateDelivery,' . RouteParameters::ORDER);

                $route->prefix('curri')->group(function(Router $route) {
                    $route->patch('/', [CurriController::class, 'update'])
                        ->name(RouteNames::API_V3_ORDER_DELIVERY_CURRI_UPDATE)
                        ->middleware('can:confirmCurriOrder,' . RouteParameters::ORDER);
                    $route->post('confirm', CurriConfirmController::class)
                        ->name(RouteNames::API_V3_ORDER_DELIVERY_CURRI_CONFIRM_STORE)
                        ->middleware('can:confirmCurriOrder,' . RouteParameters::ORDER);
                });
            });
        });
    });

    $route->prefix('order-suppliers')->group(function(Router $route) {
        $route->get('/', [OrderSupplierController::class, 'index'])->name(RouteNames::API_V3_ORDER_SUPPLIER_INDEX);
    });

    $route->prefix('parts')->group(function(Router $route) {
        $route->get('/', [PartController::class, 'index'])->name(RouteNames::API_V3_PART_INDEX);
        $route->prefix('{' . RouteParameters::PART . '}')->group(function(Router $route) {
            $route->get('/', [PartController::class, 'show'])->name(RouteNames::API_V3_PART_SHOW);
        });
    });

    $route->prefix('points')->group(function(Router $route) {
        $route->prefix('vouchers')->group(function(Router $route) {
            $route->get('/', [XoxoVoucherController::class, 'index'])->name(RouteNames::API_V3_POINTS_VOUCHERS_INDEX);
            $route->prefix('/{' . RouteParameters::VOUCHER . '}')->group(function(Router $route) {
                $route->get('/', [XoxoVoucherController::class, 'show'])->name(RouteNames::API_V3_POINTS_VOUCHERS_SHOW);
            });
        });
    });

    $route->prefix('posts')->group(function(Router $route) {
        $route->get('/', [PostController::class, 'index'])->name(RouteNames::API_V3_POST_INDEX);
        $route->post('/', [PostController::class, 'store'])->name(RouteNames::API_V3_POST_STORE);
        $route->prefix('{' . RouteParameters::POST . '}')->group(function(Router $route) {
            $route->patch('/', [PostController::class, 'update'])
                ->name(RouteNames::API_V3_POST_UPDATE)
                ->middleware('can:update,' . RouteParameters::POST);

            $route->prefix('comments')->group(function(Router $route) {
                $route->post('/', [CommentController::class, 'store'])->name(RouteNames::API_V3_POST_COMMENT_STORE);

                $route->prefix('{' . RouteParameters::COMMENT . ':' . Comment::routeKeyName() . '}')->group(function(
                    Router $route
                ) {
                    $route->patch('/', [CommentController::class, 'update'])
                        ->name(RouteNames::API_V3_POST_COMMENT_UPDATE)
                        ->middleware('can:update,' . RouteParameters::COMMENT);
                });
            });

            $route->prefix('pin')->group(function(Router $route) {
                $route->post('/', [PinController::class, 'store'])
                    ->name(RouteNames::API_V3_POST_PIN_STORE)
                    ->middleware('can:pin,' . RouteParameters::POST);
                $route->delete('/', [PinController::class, 'delete'])
                    ->name(RouteNames::API_V3_POST_PIN_DELETE)
                    ->middleware('can:unpin,' . RouteParameters::POST);
            });

            $route->get('users', PostUserController::class)->name(RouteNames::API_V3_POST_USER_INDEX);

            $route->prefix('vote')->group(function(Router $route) {
                $route->post('/', [PostVoteController::class, 'store'])->name(RouteNames::API_V3_POST_VOTE_STORE);
                $route->delete('/', [PostVoteController::class, 'delete'])->name(RouteNames::API_V3_POST_VOTE_DELETE);
            });
        });
    });

    $route->prefix('suppliers')->group(function(Router $route) {
        $route->get('/', [SupplierController::class, 'index'])->name(RouteNames::API_V3_SUPPLIER_INDEX);

        $route->prefix('/{' . RouteParameters::SUPPLIER . '}')->group(function(Router $route) {
            $route->get('/', [SupplierController::class, 'show'])->name(RouteNames::API_V3_SUPPLIER_SHOW);
            $route->post('change-request', ChangeRequestController::class)
                ->name(RouteNames::API_V3_SUPPLIER_CHANGE_REQUEST);
            $route->post('invite', InviteController::class)
                ->name(RouteNames::API_V3_SUPPLIER_INVITE)
                ->middleware(ValidateSupplierInvitation::class);
            $route->post('new-message', NewMessageController::class)->name(RouteNames::API_V3_SUPPLIER_NEW_MESSAGE);
        });
    });

    $route->prefix('supplies')->group(function(Router $route) {
        $route->get('/', [SupplyController::class, 'index'])->name(RouteNames::API_V3_SUPPLY_INDEX);
        $route->get('search', SupplySearchController::class)->name(RouteNames::API_V3_SUPPLY_SEARCH_INDEX);
    });

    $route->prefix('supply-categories')->group(function(Router $route) {
        $route->get('/', [SupplyCategoryController::class, 'index'])->name(RouteNames::API_V3_SUPPLY_CATEGORY_INDEX);

        $route->prefix('/{' . RouteParameters::SUPPLY_CATEGORY . '}')->group(function(Router $route) {
            $route->prefix('subcategories')->group(function(Router $route) {
                $route->get('/', [SupplySubcategoryController::class, 'index'])
                    ->name(RouteNames::API_V3_SUPPLY_CATEGORY_SUBCATEGORY_INDEX);
            });
        });
    });

    $route->prefix('support-calls')->group(function(Router $route) {
        $route->post('/', [SupportCallController::class, 'store'])
            ->name(RouteNames::API_V3_SUPPORT_CALL_STORE)
            ->middleware(ValidatePointsOnSupportCall::class);
    });

    $route->prefix('support-call-categories')->group(function(Router $route) {
        $route->get('/', [SupportCallCategoryController::class, 'index'])
            ->name(RouteNames::API_V3_SUPPORT_CALL_CATEGORY_INDEX)
            ->middleware(ValidatePointsOnSupportCall::class);

        $route->prefix('/{' . RouteParameters::SUPPORT_CALL_CATEGORY . '}')->group(function(Router $route) {
            $route->prefix('subcategories')->group(function(Router $route) {
                $route->get('/', [SupportCallSubcategoryController::class, 'index'])
                    ->name(RouteNames::API_V3_SUPPORT_CALL_CATEGORY_SUBCATEGORY_INDEX);
            });
        });
    });

    $route->prefix('users')->group(function(Router $route) {
        $route->get('/', [UserController::class, 'index'])->name(RouteNames::API_V3_USER_INDEX);

        $route->prefix('/{' . RouteParameters::USER . '}')->group(function(Router $route) {
            $route->get('/', [UserController::class, 'show'])->name(RouteNames::API_V3_USER_SHOW);
            $route->get('posts', UserPostController::class)->name(RouteNames::API_V3_USER_POST_INDEX);
        });
    });
});

Route::get('app-version', AppVersionController::class)->name(RouteNames::API_V3_APP_VERSION);

Route::prefix('auth')->group(function(Router $route) {
    $route->prefix('create-password')->group(function(Router $route) {
        $route->post('/', [CreatePasswordController::class, 'store'])->name(RouteNames::API_V3_AUTH_CREATE_PASSWORD);
    });

    $route->prefix('email')->group(function(Router $route) {
        $route->post('login', LoginController::class)->name(RouteNames::API_V3_AUTH_EMAIL_LOGIN);
    });

    $route->delete('logout', LogoutController::class)
        ->name(RouteNames::API_V3_AUTH_LOGOUT)
        ->middleware(AuthenticateUser::class);

    $route->prefix('phone')->group(function(Router $route) {
        $route->prefix('{' . RouteParameters::ASSIGNED_VERIFIED_PHONE . '}')->group(function(Router $route) {
            $route->prefix('login')->group(function(Router $route) {
                $route->post('call', AuthPhoneLoginCallController::class)
                    ->name(RouteNames::API_V3_AUTH_PHONE_LOGIN_CALL);
                $route->post('sms', AuthPhoneLoginSmsController::class)
                    ->name(RouteNames::API_V3_AUTH_PHONE_LOGIN_SMS)
                    ->middleware(ValidateIfPhoneCanMakeSMSRequests::class);
                $route->post('verify', AuthPhoneLoginVerifyController::class)
                    ->name(RouteNames::API_V3_AUTH_PHONE_LOGIN_VERIFY);
            });
        });

        $route->prefix('{' . RouteParameters::UNVERIFIED_PHONE . '}')->group(function(Router $route) {
            $route->prefix('register')->group(function(Router $route) {
                $route->post('verify', AuthPhoneRegisterVerifyController::class)
                    ->name(RouteNames::API_V3_AUTH_PHONE_REGISTER_VERIFY);
            });
        });

        $route->prefix('register')->group(function(Router $route) {
            $route->post('assign', AssignController::class)
                ->name(RouteNames::API_V3_AUTH_PHONE_REGISTER_ASSIGN)
                ->middleware([ProvideLatamPhone::class, AuthenticatePhone::class]);
            $route->post('call', AuthPhoneRegisterCallController::class)
                ->name(RouteNames::API_V3_AUTH_PHONE_REGISTER_CALL);
            $route->post('sms', AuthPhoneRegisterSmsController::class)
                ->name(RouteNames::API_V3_AUTH_PHONE_REGISTER_SMS);
        });
    });

    $route->post('refresh', RefreshController::class)
        ->name(RouteNames::API_V3_AUTH_REFRESH)
        ->middleware(AuthenticateUser::class);
});

Route::prefix('twilio')->middleware(ValidateTwilioRequest::class)->group(function(Router $route) {
    $route->prefix('webhooks')->group(function(Router $route) {
        $route->prefix('auth')->group(function(Router $route) {
            $route->prefix('call')->group(function(Router $route) {
                $route->post('/', TwilioWebhooksAuthCallController::class)
                    ->name(RouteNames::API_V3_TWILIO_WEBHOOK_AUTH_CALL);
                $route->post('complete', TwilioWebhooksAuthCallCompleteController::class)
                    ->name(RouteNames::API_V3_TWILIO_WEBHOOK_AUTH_CALL_COMPLETE);
                $route->post('fallback', TwilioWebhooksAuthCallFallbackController::class)
                    ->name(RouteNames::API_V3_TWILIO_WEBHOOK_AUTH_CALL_FALLBACK);
            });
        });
    });
});
