<?php

namespace Tests\Unit;

use App\Constants\RouteParameters;
use App\Constants\RoutePrefixes;
use Illuminate\Routing\Route;
use Illuminate\Support\Collection;
use Str;
use Tests\TestCase;

class RoutesTest extends TestCase
{
    const AUTOMATION_V1_ROUTES = 'automationV1Routes';
    const BASECAMP_V1_ROUTES   = 'basecampV1Routes';
    const LIVE_V1_ROUTES       = 'liveV1Routes';
    const V3_ROUTES            = 'v3Routes';
    const V4_ROUTES            = 'v4Routes';
    const WEBHOOK_V1_ROUTES    = 'webhookV1Routes';

    private Collection $routes;
    private Collection $groupedRegisteredRoutes;

    public function setUp(): void
    {
        parent::setUp();
        $this->groupedRegisteredRoutes = Collection::make();
        $this->routes                  = Collection::make(\Route::getRoutes()->getRoutes());
        $v3Routes                      = $this->routes->filter(function(Route $a) {
            return Str::startsWith($a->uri(), RoutePrefixes::API . '/v3');
        });
        $v4Routes                      = $this->routes->filter(function(Route $a) {
            return Str::startsWith($a->uri(), RoutePrefixes::API . '/v4');
        });
        $liveV1Routes                  = $this->routes->filter(function(Route $a) {
            return Str::startsWith($a->uri(), RoutePrefixes::LIVE . '/v1');
        });
        $automationV1Routes            = $this->routes->filter(function(Route $a) {
            return Str::startsWith($a->uri(), RoutePrefixes::AUTOMATION . '/v1');
        });
        $webhookV1Routes               = $this->routes->filter(function(Route $a) {
            return Str::startsWith($a->uri(), RoutePrefixes::WEBHOOK . '/v1');
        });
        $basecampV1Routes              = $this->routes->filter(function(Route $a) {
            return Str::startsWith($a->uri(), RoutePrefixes::BASECAMP . '/v1');
        });

        $baseRoutes = Collection::make();
        $v3Routes->each(function(Route $route) use ($baseRoutes) {
            $uri     = Str::substr($route->uri(), Str::length(RoutePrefixes::API . '/v3/'));
            $methods = Collection::make($baseRoutes[$uri] ?? []);
            foreach ($route->methods() as $method) {
                if (in_array($method, ['HEAD', 'OPTIONS'])) {
                    continue;
                }
                $methods->put($method, $method);
            }
            $baseRoutes->put($uri, $methods);
        });
        $this->groupedRegisteredRoutes->put(self::V3_ROUTES, $baseRoutes);

        $baseRoutesV4 = Collection::make();
        $v4Routes->each(function(Route $route) use ($baseRoutesV4) {
            $uri     = Str::substr($route->uri(), Str::length(RoutePrefixes::API . '/v4/'));
            $methods = Collection::make($baseRoutesV4[$uri] ?? []);
            foreach ($route->methods() as $method) {
                if (in_array($method, ['HEAD', 'OPTIONS'])) {
                    continue;
                }
                $methods->put($method, $method);
            }
            $baseRoutesV4->put($uri, $methods);
        });
        $this->groupedRegisteredRoutes->put(self::V4_ROUTES, $baseRoutesV4);

        $baseLiveV1Routes = Collection::make();
        $liveV1Routes->each(function(Route $route) use ($baseLiveV1Routes) {
            $uri     = Str::substr($route->uri(), Str::length(RoutePrefixes::LIVE . '/v1/'));
            $methods = Collection::make($baseLiveV1Routes[$uri] ?? []);
            foreach ($route->methods() as $method) {
                if (in_array($method, ['HEAD', 'OPTIONS'])) {
                    continue;
                }
                $methods->put($method, $method);
            }
            $baseLiveV1Routes->put($uri, $methods);
        });
        $this->groupedRegisteredRoutes->put(self::LIVE_V1_ROUTES, $baseLiveV1Routes);

        $baseAutomationV1Routes = Collection::make();
        $automationV1Routes->each(function(Route $route) use ($baseAutomationV1Routes) {
            $uri     = Str::substr($route->uri(), Str::length(RoutePrefixes::AUTOMATION . '/v1/'));
            $methods = Collection::make($baseAutomationV1Routes[$uri] ?? []);
            foreach ($route->methods() as $method) {
                if (in_array($method, ['HEAD', 'OPTIONS'])) {
                    continue;
                }
                $methods->put($method, $method);
            }
            $baseAutomationV1Routes->put($uri, $methods);
        });
        $this->groupedRegisteredRoutes->put(self::AUTOMATION_V1_ROUTES, $baseAutomationV1Routes);

        $baseWebhookV1Routes = Collection::make();
        $webhookV1Routes->each(function(Route $route) use ($baseWebhookV1Routes) {
            $uri     = Str::substr($route->uri(), Str::length(RoutePrefixes::WEBHOOK . '/v1/'));
            $methods = Collection::make($baseWebhookV1Routes[$uri] ?? []);
            foreach ($route->methods() as $method) {
                if (in_array($method, ['HEAD', 'OPTIONS'])) {
                    continue;
                }
                $methods->put($method, $method);
            }
            $baseWebhookV1Routes->put($uri, $methods);
        });
        $this->groupedRegisteredRoutes->put(self::WEBHOOK_V1_ROUTES, $baseWebhookV1Routes);

        $baseBasecampV1Routes = Collection::make();
        $basecampV1Routes->each(function(Route $route) use ($baseBasecampV1Routes) {
            $uri     = Str::substr($route->uri(), Str::length(RoutePrefixes::BASECAMP . '/v1/'));
            $methods = Collection::make($baseBasecampV1Routes[$uri] ?? []);
            foreach ($route->methods() as $method) {
                if (in_array($method, ['HEAD', 'OPTIONS'])) {
                    continue;
                }
                $methods->put($method, $method);
            }
            $baseBasecampV1Routes->put($uri, $methods);
        });
        $this->groupedRegisteredRoutes->put(self::BASECAMP_V1_ROUTES, $baseBasecampV1Routes);
    }

    /**
     * @test
     *
     * @param array  $methods
     * @param string $uri
     *
     * @dataProvider dataProvider
     */
    public function it_should_have_a_specific_path_and_verb(array $methods, string $uri)
    {
        $this->assertNotNull($this->routes->first(function(Route $route) use ($methods, $uri) {
            $uriCondition    = $route->uri() == $uri;
            $methodCondition = !array_diff($methods, $route->methods());

            return $uriCondition && $methodCondition;
        }), 'Route: ' . implode('|', $methods) . ' ' . $uri . ' not found.');
    }

    public function dataProvider(): array
    {
        $comment              = RouteParameters::COMMENT;
        $internalNotification = RouteParameters::INTERNAL_NOTIFICATION;
        $post                 = RouteParameters::POST;
        $product              = RouteParameters::PRODUCT;
        $settingUser          = RouteParameters::SETTING_USER;
        $taggable             = RouteParameters::TAGGABLE;
        $ticket               = RouteParameters::TICKET;

        return [
            [['DELETE'], 'api/v2/posts/{' . $post . '}'],
            [['DELETE'], 'api/v2/posts/{' . $post . '}/comments/{' . $comment . '}'],
            [['DELETE'], 'api/v2/posts/{' . $post . '}/comments/{' . $comment . '}/vote'],
            [['DELETE'], 'api/v2/posts/{' . $post . '}/solution/{' . $comment . '}'],
            [['DELETE'], 'api/v2/tags/{' . $taggable . '}/follow'],
            [['GET'], 'api/brands'],
            [['GET'], 'api/layout/{version}'],
            [['GET'], 'api/products/{product}'],
            [['GET'], 'api/products/{product}/conversion'],
            [['GET'], 'api/products/{product}/warnings'],
            [['GET'], 'api/v2/activity'],
            [['GET'], 'api/v2/address/search'],
            [['GET'], 'api/v2/agents'],
            [['GET'], 'api/v2/allbrands'],
            [['GET'], 'api/v2/brands'],
            [['GET'], 'api/v2/brands/{brandId}/series'],
            [['GET'], 'api/v2/brands/{brandId}/series/{seriesId}/products'],
            [['GET'], 'api/v2/countries'],
            [['GET'], 'api/v2/internal-notifications'],
            [['GET'], 'api/v2/internal-notifications/{' . $internalNotification . '}'],
            [['GET'], 'api/v2/layout/{version}'],
            [['GET'], 'api/v2/logout'],
            [['GET'], 'api/v2/me'],
            [['GET'], 'api/v2/notifications'],
            [['GET'], 'api/v2/notifications/status'],
            [['GET'], 'api/v2/posts'],
            [['GET'], 'api/v2/posts/{' . $post . '}'],
            [['GET'], 'api/v2/posts/{' . $post . '}/comments'],
            [['GET'], 'api/v2/products/{product}/conversion'],
            [['GET'], 'api/v2/products/{product}/manuals'],
            [['GET'], 'api/v2/products/{product}/warnings'],
            [['GET'], 'api/v2/products/{' . $product . '}'],
            [['GET'], 'api/v2/refresh'],
            [['GET'], 'api/v2/reviews'],
            [['GET'], 'api/v2/reviews/media'],
            [['GET'], 'api/v2/reviews/{review}'],
            [['GET'], 'api/v2/stores'],
            [['GET'], 'api/v2/stores/search'],
            [['GET'], 'api/v2/stores/search/place'],
            [['GET'], 'api/v2/support/ticket/agent-history'],
            [['GET'], 'api/v2/support/topics'],
            [['GET'], 'api/v2/support/{code}/technician'],
            [['GET'], 'api/v2/tags'],
            [['GET'], 'api/v2/tags/{' . $taggable . '}'],
            [['GET'], 'api/v2/users/count'],
            [['GET'], 'api/v2/users/count/accreditated'],
            [['GET'], 'api/v2/user'],
            [['GET'], 'api/v2/user/followed-posts'],
            [['GET'], 'api/v2/{brand}/products'],
            [['GET'], 'api/v2/{brand}/products/search'],
            [['GET'], 'api/{brand}/products'],
            [['GET'], 'api/{brand}/products/search'],
            [['PATCH'], 'api/v2/posts/{' . $post . '}'],
            [['PATCH'], 'api/v2/posts/{' . $post . '}/comments/{' . $comment . '}'],
            [['PATCH'], 'api/v2/user/settings/{' . $settingUser . '}'],
            [['POST'], 'api/v2/accept-terms'],
            [['POST'], 'api/v2/complete-accreditation'],
            [['POST'], 'api/v2/complete-registration'],
            [['POST'], 'api/v2/create-account'],
            [['POST'], 'api/v2/legacy/login'],
            [['POST'], 'api/v2/login'],
            [['POST'], 'api/v2/notifications/create'],
            [['POST'], 'api/v2/notifications/read'],
            [['POST'], 'api/v2/notifications/remove'],
            [['POST'], 'api/v2/posts'],
            [['POST'], 'api/v2/posts/{' . $post . '}/comments'],
            [['POST'], 'api/v2/posts/{' . $post . '}/comments/{' . $comment . '}/vote'],
            [['POST'], 'api/v2/posts/{' . $post . '}/solution'],
            [['POST'], 'api/v2/push-notification-token'],
            [['POST'], 'api/v2/reset-password'],
            [['POST'], 'api/v2/statistics/update'],
            [['POST'], 'api/v2/support/ticket/{' . $ticket . '}/agent-rate'],
            [['POST'], 'api/v2/support/ticket/{' . $ticket . '}/close'],
            [['POST'], 'api/v2/support/ticket/{' . $ticket . '}/rate'],
            [['POST'], 'api/v2/tags/{' . $taggable . '}/follow'],
            [['POST'], 'api/v2/twilio/token'],
            [['POST'], 'api/v2/twilio/webhook/call'],
            [['POST'], 'api/v2/twilio/webhook/call/action'],
            [['POST'], 'api/v2/twilio/webhook/call/client/status'],
            [['POST'], 'api/v2/twilio/webhook/call/complete'],
            [['POST'], 'api/v2/twilio/webhook/call/fallback'],
            [['POST'], 'api/v2/update-call-date'],
            [['POST'], 'api/v2/user'],
        ];
    }

    /**
     * @test
     *
     * @param string     $routePrefix
     * @param string     $registeredRoutes
     * @param Collection $ourRoutes
     *
     * @dataProvider registeredRoutesProvider
     */
    public function registered_routes_should_be_tested(
        string $routePrefix,
        string $registeredRoutes,
        Collection $ourRoutes
    ) {
        $failed = Collection::make();
        $this->groupedRegisteredRoutes->get($registeredRoutes)->each(function(Collection $methods, string $uri) use (
            $routePrefix,
            $ourRoutes,
            $failed
        ) {
            if (!$ourRoutes->has($uri)) {
                $failed->push("Failed asserting that $routePrefix/$uri is tested.");

                return;
            }

            /** @var Collection $ourMethods */
            $ourMethods = $ourRoutes->get($uri);
            $methods->each(function(string $method) use ($uri, $ourMethods, $failed) {
                if (!$ourMethods->contains($method)) {
                    $failed->push("Failed asserting that api/v3/$uri has method $method.");
                }
            });
        });

        $messages = $failed->implode("\n");
        $this->assertEmpty($failed, $messages);
    }

    private function ourV3Routes(): Collection
    {
        $appSetting            = RouteParameters::APP_SETTING;
        $assignedVerifiedPhone = RouteParameters::ASSIGNED_VERIFIED_PHONE;
        $brand                 = RouteParameters::BRAND;
        $cartItem              = RouteParameters::CART_ITEM;
        $comment               = RouteParameters::COMMENT;
        $country               = RouteParameters::COUNTRY;
        $internalNotification  = RouteParameters::INTERNAL_NOTIFICATION;
        $itemWishlist          = RouteParameters::ITEM_WISHLIST;
        $modelType             = RouteParameters::MODEL_TYPE;
        $note                  = RouteParameters::NOTE;
        $noteCategory          = RouteParameters::NOTE_CATEGORY;
        $oem                   = RouteParameters::OEM;
        $order                 = RouteParameters::ORDER;
        $part                  = RouteParameters::PART;
        $post                  = RouteParameters::POST;
        $product               = RouteParameters::PRODUCT;
        $series                = RouteParameters::SERIES;
        $settingUser           = RouteParameters::SETTING_USER;
        $supplier              = RouteParameters::SUPPLIER;
        $supplyCategory        = RouteParameters::SUPPLY_CATEGORY;
        $supportCallCategory   = RouteParameters::SUPPORT_CALL_CATEGORY;
        $taggable              = RouteParameters::TAGGABLE;
        $ticket                = RouteParameters::TICKET;
        $unverifiedPhone       = RouteParameters::UNVERIFIED_PHONE;
        $user                  = RouteParameters::USER;
        $voucher               = RouteParameters::VOUCHER;
        $wishlist              = RouteParameters::WISHLIST;

        return Collection::make([
            'account'                                                   => Collection::make(['GET', 'DELETE']),
            'account/bulk-favorite-series'                              => Collection::make(['POST']),
            'account/bulk-suppliers'                                    => Collection::make(['POST']),
            'account/brief-suppliers'                                   => Collection::make(['GET']),
            'account/channels'                                          => Collection::make(['GET']),
            'account/cart/items'                                        => Collection::make(['GET', 'POST']),
            'account/cart/items/{' . $cartItem . '}'                    => Collection::make(['PATCH', 'DELETE']),
            'account/grouped-suppliers'                                 => Collection::make(['GET']),
            'account/oems'                                              => Collection::make(['GET', 'POST']),
            'account/oems/{' . $oem . '}'                               => Collection::make(['DELETE']),
            'account/oems/recently-viewed'                              => Collection::make(['GET']),
            'account/recently-viewed'                                   => Collection::make(['GET']),
            'account/parts/recently-viewed'                             => Collection::make(['GET']),
            'account/phones/call'                                       => Collection::make(['POST']),
            'account/phones/sms'                                        => Collection::make(['POST']),
            'account/phones/{' . $unverifiedPhone . '}/verify'          => Collection::make(['POST']),
            'account/points'                                            => Collection::make(['GET']),
            'account/points/redemptions'                                => Collection::make(['GET']),
            'account/points/vouchers/{' . $voucher . '}/redeem'         => Collection::make(['POST']),
            'account/profile'                                           => Collection::make(['GET', 'PATCH']),
            'account/suppliers'                                         => Collection::make(['GET', 'POST']),
            'account/suppliers/channels'                                => Collection::make(['GET']),
            'account/supplies/recently-added'                           => Collection::make(['GET']),
            'account/terms/accept'                                      => Collection::make(['POST']),
            'account/verified-suppliers'                                => Collection::make(['GET']),
            'account/wishlists'                                         => Collection::make(['GET', 'POST']),
            'account/wishlists/{' . $wishlist . '}'                     => Collection::make(['PATCH', 'DELETE']),
            'account/wishlists/{' . $wishlist . '}/items'               => Collection::make(['GET', 'POST']),
            'activity'                                                  => Collection::make(['GET']),
            'address/countries'                                         => Collection::make(['GET']),
            'address/countries/{' . $country . '}/states'               => Collection::make(['GET']),
            'app-settings/{' . $appSetting . '}'                        => Collection::make(['GET']),
            'app-version'                                               => Collection::make(['GET']),
            'app-version/confirm'                                       => Collection::make(['POST']),
            'auth/create-password'                                      => Collection::make(['POST']),
            'auth/email/login'                                          => Collection::make(['POST']),
            'auth/logout'                                               => Collection::make(['DELETE']),
            'auth/phone/register/assign'                                => Collection::make(['POST']),
            'auth/phone/register/call'                                  => Collection::make(['POST']),
            'auth/phone/register/sms'                                   => Collection::make(['POST']),
            'auth/phone/{' . $assignedVerifiedPhone . '}/login/call'    => Collection::make(['POST']),
            'auth/phone/{' . $assignedVerifiedPhone . '}/login/sms'     => Collection::make(['POST']),
            'auth/phone/{' . $assignedVerifiedPhone . '}/login/verify'  => Collection::make(['POST']),
            'auth/phone/{' . $unverifiedPhone . '}/register/verify'     => Collection::make(['POST']),
            'auth/refresh'                                              => Collection::make(['POST']),
            'brands'                                                    => Collection::make(['GET']),
            'brands/most-searched'                                      => Collection::make(['GET']),
            'brands/{' . $brand . '}/series'                            => Collection::make(['GET']),
            'brands/{' . $brand . '}/series/{' . $series . '}/oems'     => Collection::make(['GET']),
            'custom-items'                                              => Collection::make(['POST']),
            'internal-notifications'                                    => Collection::make(['GET']),
            'internal-notifications/{' . $internalNotification . '}'    => Collection::make(['GET']),
            'internal-notifications/mark-as-read'                       => Collection::make(['PATCH']),
            'model-types'                                               => Collection::make(['GET']),
            'model-types/{' . $modelType . '}/brands'                   => Collection::make(['GET']),
            'oems'                                                      => Collection::make(['GET']),
            'oems/{' . $oem . '}'                                       => Collection::make(['GET']),
            'oems/{' . $oem . '}/parts'                                 => Collection::make(['GET']),
            'orders'                                                    => Collection::make(['GET', 'POST']),
            'orders/{' . $order . '}'                                   => Collection::make(['GET']),
            'orders/{' . $order . '}/approve'                           => Collection::make(['POST']),
            'orders/{' . $order . '}/delivery'                          => Collection::make(['PATCH']),
            'orders/{' . $order . '}/delivery/curri'                    => Collection::make(['PATCH']),
            'orders/{' . $order . '}/delivery/curri/confirm'            => Collection::make(['POST']),
            'orders/{' . $order . '}/cancel'                            => Collection::make(['POST']),
            'orders/{' . $order . '}/items'                             => Collection::make(['GET']),
            'orders/{' . $order . '}/share'                             => Collection::make(['POST']),
            'order-suppliers'                                           => Collection::make(['GET']),
            'parts'                                                     => Collection::make(['GET']),
            'parts/{' . $part . '}'                                     => Collection::make(['GET']),
            'points/vouchers'                                           => Collection::make(['GET']),
            'points/vouchers/{' . $voucher . '}'                        => Collection::make(['GET']),
            'posts'                                                     => Collection::make(['GET', 'POST']),
            'posts/{' . $post . '}'                                     => Collection::make(['GET', 'PATCH', 'DELETE']),
            'posts/{' . $post . '}/comments'                            => Collection::make(['GET', 'POST']),
            'posts/{' . $post . '}/comments/{' . $comment . '}'         => Collection::make(['PATCH', 'DELETE']),
            'posts/{' . $post . '}/comments/{' . $comment . '}/vote'    => Collection::make(['POST', 'DELETE']),
            'posts/{' . $post . '}/pin'                                 => Collection::make(['POST', 'DELETE']),
            'posts/{' . $post . '}/solution'                            => Collection::make(['POST']),
            'posts/{' . $post . '}/solution/{' . $comment . '}'         => Collection::make(['DELETE']),
            'posts/{' . $post . '}/users'                               => Collection::make(['GET']),
            'posts/{' . $post . '}/vote'                                => Collection::make(['POST', 'DELETE']),
            'products/{' . $product . '}'                               => Collection::make(['GET']),
            'push-notification-token'                                   => Collection::make(['POST']),
            'suppliers'                                                 => Collection::make(['GET']),
            'suppliers/{' . $supplier . '}'                             => Collection::make(['GET']),
            'suppliers/{' . $supplier . '}/change-request'              => Collection::make(['POST']),
            'suppliers/{' . $supplier . '}/invite'                      => Collection::make(['POST']),
            'suppliers/{' . $supplier . '}/new-message'                 => Collection::make(['POST']),
            'supplies'                                                  => Collection::make(['GET']),
            'supplies/search'                                           => Collection::make(['GET']),
            'supply-categories'                                         => Collection::make(['GET']),
            'supply-categories/{' . $supplyCategory . '}/subcategories' => Collection::make(['GET']),
            'support-calls'                                             => Collection::make(['POST']),
            'support/technicians'                                       => Collection::make(['GET']),
            'support/ticket/agent-history'                              => Collection::make(['GET']),
            'support/ticket/{' . $ticket . '}/agent-rate'               => Collection::make(['POST']),
            'support/ticket/{' . $ticket . '}/close'                    => Collection::make(['POST']),
            'support/ticket/{' . $ticket . '}/rate'                     => Collection::make(['POST']),
            'tags'                                                      => Collection::make(['GET']),
            'tags/{' . $taggable . '}'                                  => Collection::make(['GET']),
            'tags/{' . $taggable . '}/follow'                           => Collection::make(['POST', 'DELETE']),
            'twilio/token'                                              => Collection::make(['POST']),
            'twilio/webhooks/auth/call'                                 => Collection::make(['POST']),
            'twilio/webhooks/auth/call/complete'                        => Collection::make(['POST']),
            'twilio/webhooks/auth/call/fallback'                        => Collection::make(['POST']),
            'twilio/webhook/call'                                       => Collection::make(['POST']),
            'twilio/webhook/call/action'                                => Collection::make(['POST']),
            'twilio/webhook/call/client/status'                         => Collection::make(['POST']),
            'twilio/webhook/call/complete'                              => Collection::make(['POST']),
            'twilio/webhook/call/fallback'                              => Collection::make(['POST']),
            'user/followed-posts'                                       => Collection::make(['GET']),
            'user/settings/{' . $settingUser . '}'                      => Collection::make(['PATCH']),
            'users'                                                     => Collection::make(['GET']),
            'users/count'                                               => Collection::make(['GET']),
            'users/{' . $user . '}'                                     => Collection::make(['GET']),
            'users/{' . $user . '}/posts'                               => Collection::make(['GET']),

            'account/cart' => Collection::make(['GET', 'POST', 'DELETE']),

            'account/wishlists/{' . $wishlist . '}/items/{' . $itemWishlist . '}' => Collection::make([
                'PATCH',
                'DELETE',
            ]),

            'note-categories/{' . $noteCategory . '}/notes'                                          => Collection::make(['GET']),
            'note-categories/{' . $noteCategory . '}/notes/{' . $note . '}'                          => Collection::make(['GET']),
            'model-types/{' . $modelType . '}/brands/{' . $brand . '}/series'                        => Collection::make(['GET']),
            'model-types/{' . $modelType . '}/brands/{' . $brand . '}/series/{' . $series . '}/oems' => Collection::make(['GET']),
            'support-call-categories'                                                                => Collection::make(['GET']),
            'support-call-categories/{' . $supportCallCategory . '}/subcategories'                   => Collection::make(['GET']),
        ]);
    }

    private function ourV4Routes(): Collection
    {
        $cartItem       = RouteParameters::CART_ITEM;
        $order          = RouteParameters::ORDER;
        $supplier       = RouteParameters::SUPPLIER;
        $supplyCategory = RouteParameters::SUPPLY_CATEGORY;

        return Collection::make([
            'account/cart'                                      => Collection::make(['GET', 'POST', 'PATCH', 'DELETE']),
            'account/cart/items'                                => Collection::make(['GET', 'POST']),
            'account/cart/items/{' . $cartItem . '}'            => Collection::make(['PATCH', 'DELETE']),
            'account/channels'                                  => Collection::make(['GET']),
            'account/companies'                                 => Collection::make(['POST', 'PATCH']),
            'account/profile'                                   => Collection::make(['GET', 'PATCH']),
            'account/suppliers/{' . $supplier . '}/orders'      => Collection::make(['GET']),
            'companies'                                         => Collection::make(['GET']),
            'custom-items'                                      => Collection::make(['POST']),
            'orders'                                            => Collection::make(['GET', 'POST']),
            'orders/active'                                     => Collection::make(['GET']),
            'orders/{' . $order . '}'                           => Collection::make(['GET']),
            'orders/{' . $order . '}/approve'                   => Collection::make(['POST']),
            'orders/{' . $order . '}/confirm-total'             => Collection::make(['POST']),
            'orders/{' . $order . '}/delivery'                  => Collection::make(['POST']),
            'orders/{' . $order . '}/delivery/address'          => Collection::make(['POST']),
            'orders/{' . $order . '}/delivery/pickup/confirm'   => Collection::make(['POST']),
            'orders/{' . $order . '}/delivery/shipment/approve' => Collection::make(['POST']),
            'orders/{' . $order . '}/items'                     => Collection::make(['GET']),
            'orders/{' . $order . '}/cancel'                    => Collection::make(['POST']),
            'orders/{' . $order . '}/complete'                  => Collection::make(['POST']),
            'orders/{' . $order . '}/extra-items'               => Collection::make(['POST']),
            'orders/{' . $order . '}/name'                      => Collection::make(['POST']),
            'suppliers/default'                                 => Collection::make(['GET']),
            'suppliers/{' . $supplier . '}'                     => Collection::make(['GET']),
            'suppliers/{' . $supplier . '}/new-message'         => Collection::make(['POST']),
            'supplies'                                          => Collection::make(['GET']),
            'supplies/search'                                   => Collection::make(['GET']),
            'supply-categories'                                 => Collection::make(['GET']),
            'support-calls'                                     => Collection::make(['POST']),

            'supply-categories/{' . $supplyCategory . '}/subcategories' => Collection::make(['GET']),
        ]);
    }

    private function ourLiveV1Routes(): Collection
    {
        $appSetting           = RouteParameters::APP_SETTING;
        $brand                = RouteParameters::BRAND;
        $channel              = RouteParameters::CHANNEL;
        $country              = RouteParameters::COUNTRY;
        $itemOrder            = RouteParameters::ITEM_ORDER;
        $oem                  = RouteParameters::OEM;
        $order                = RouteParameters::ORDER;
        $part                 = RouteParameters::PART;
        $partItemOrder        = RouteParameters::PART_ITEM_ORDER;
        $series               = RouteParameters::SERIES;
        $settingSupplier      = RouteParameters::SETTING_SUPPLIER;
        $unauthenticatedOrder = RouteParameters::UNAUTHENTICATED_ORDER;
        $user                 = RouteParameters::USER;

        return Collection::make([
            'address/countries'                                     => Collection::make(['GET']),
            'address/countries/{' . $country . '}/states'           => Collection::make(['GET']),
            'app-settings/{' . $appSetting . '}'                    => Collection::make(['GET']),
            'auth/email/forgot-password'                            => Collection::make(['POST']),
            'auth/email/initial-password'                           => Collection::make(['POST']),
            'auth/email/login'                                      => Collection::make(['POST']),
            'auth/email/reset-password'                             => Collection::make(['POST']),
            'brands'                                                => Collection::make(['GET']),
            'brands/{' . $brand . '}/series'                        => Collection::make(['GET']),
            'brands/{' . $brand . '}/series/{' . $series . '}/oems' => Collection::make(['GET']),
            'confirmed-users/{' . $user . '}/confirm'               => Collection::make(['POST', 'DELETE']),
            'limited-supplier'                                      => Collection::make(['GET']),
            'notification-settings'                                 => Collection::make(['GET', 'POST']),
            'oems'                                                  => Collection::make(['GET']),
            'oems/{' . $oem . '}'                                   => Collection::make(['GET']),
            'oems/{' . $oem . '}/parts'                             => Collection::make(['GET']),
            'orders/in-progress'                                    => Collection::make(['GET']),
            'orders/in-progress/{' . $order . '}/cancel'            => Collection::make(['POST']),
            'orders/in-progress/{' . $order . '}/delivery'          => Collection::make(['PATCH']),
            'orders/in-progress/{' . $order . '}/items'             => Collection::make(['GET']),
            'orders/{' . $order . '}/assignment'                    => Collection::make(['POST', 'DELETE']),
            'orders/{' . $order . '}/cancel'                        => Collection::make(['POST']),
            'orders/{' . $order . '}/complete'                      => Collection::make(['POST']),
            'orders/{' . $order . '}/custom-item'                   => Collection::make(['POST']),
            'orders/{' . $order . '}/delivery'                      => Collection::make(['PATCH']),
            'orders/{' . $order . '}/delivery/eta'                  => Collection::make(['PATCH']),
            'orders/{' . $order . '}/fees'                          => Collection::make(['POST']),
            'orders/{' . $order . '}/items'                         => Collection::make(['GET']),
            'orders/{' . $order . '}/items/{' . $itemOrder . '}'    => Collection::make(['GET', 'PATCH', 'DELETE']),
            'orders/{' . $order . '}/pre-approve'                   => Collection::make(['POST']),
            'orders/{' . $order . '}/reopen'                        => Collection::make(['POST']),
            'orders/{' . $order . '}/send-for-approval'             => Collection::make(['POST']),
            'parts'                                                 => Collection::make(['GET']),
            'parts/{' . $part . '}'                                 => Collection::make(['GET']),
            'parts/{' . $part . '}/recommended-replacements'        => Collection::make(['POST']),
            'parts/{' . $part . '}/replacements'                    => Collection::make(['GET']),
            'removed-users'                                         => Collection::make(['GET']),
            'removed-users/{' . $user . '}'                         => Collection::make(['POST', 'DELETE']),
            'settings'                                              => Collection::make(['GET']),
            'settings/{' . $settingSupplier . '}'                   => Collection::make(['GET']),
            'settings/bulk-notification'                            => Collection::make(['POST']),
            'supplier'                                              => Collection::make(['GET', 'PATCH']),
            'supplier/bulk-brand'                                   => Collection::make(['POST']),
            'supplier/bulk-hour'                                    => Collection::make(['POST']),
            'supplier/users'                                        => Collection::make(['GET']),
            'supplier/users/{' . $channel . '}'                     => Collection::make(['GET']),
            'users'                                                 => Collection::make(['GET']),
            'users/{' . $user . '}/confirm'                         => Collection::make(['POST']),
            'users/{' . $user . '}/new-message'                     => Collection::make(['POST']),
            'users/{' . $user . '}/orders'                          => Collection::make(['GET']),
            'confirmed-users/{user}'                                => Collection::make(['PATCH']),

            'orders/in-progress/{' . $order . '}/items/{' . $itemOrder . '}/remove'      => Collection::make(['POST']),
            'orders/in-progress/{' . $order . '}/delivery/curri/calculate-price'         => Collection::make(['POST']),
            'orders/in-progress/{' . $order . '}/delivery/curri/confirm'                 => Collection::make(['POST']),
            'orders/{' . $order . '}/items/{' . $partItemOrder . '}/replacements'        => Collection::make(['GET']),
            'orders/in-progress/{' . $order . '}/delivery/curri/notice/en-route/confirm' => Collection::make(['POST']),
            'unauthenticated/orders/{' . $unauthenticatedOrder . '}'                     => Collection::make(['GET']),
            'unauthenticated/orders/{' . $unauthenticatedOrder . '}/approve'             => Collection::make(['POST']),
            'unauthenticated/orders/{' . $unauthenticatedOrder . '}/items'               => Collection::make(['GET']),
        ]);
    }

    private function ourAutomationV1Routes(): Collection
    {
        $unverifiedPhone = RouteParameters::UNVERIFIED_PHONE;

        return Collection::make([
            'mobile/signup-process/{' . $unverifiedPhone . '}' => Collection::make(['GET']),
        ]);
    }

    private function ourWebhookV1Routes(): Collection
    {
        return Collection::make([
            'curri/tracking' => Collection::make(['POST']),
        ]);
    }

    private function ourBasecampV1Routes(): Collection
    {
        $supportCall = RouteParameters::SUPPORT_CALL;
        $user        = RouteParameters::USER;

        return Collection::make([
            'support-calls/{' . $supportCall . '}' => Collection::make(['GET']),
            'users'                                => Collection::make(['GET']),
            'users/{' . $user . '}'                => Collection::make(['GET']),
            'users/{' . $user . '}/suppliers'      => Collection::make(['GET']),
        ]);
    }

    public function registeredRoutesProvider(): array
    {
        return [
            [
                "routePrefix"      => RoutePrefixes::API . '/v3',
                "registeredRoutes" => self::V3_ROUTES,
                "ourRoutes"        => $this->ourV3Routes(),
            ],
            [
                "routePrefix"      => RoutePrefixes::API . '/v4',
                "registeredRoutes" => self::V4_ROUTES,
                "ourRoutes"        => $this->ourV4Routes(),
            ],
            [
                "routePrefix"      => RoutePrefixes::LIVE . '/v1',
                "registeredRoutes" => self::LIVE_V1_ROUTES,
                "ourRoutes"        => $this->ourLiveV1Routes(),
            ],
            [
                "routePrefix"      => RoutePrefixes::AUTOMATION . '/v1',
                "registeredRoutes" => self::AUTOMATION_V1_ROUTES,
                "ourRoutes"        => $this->ourAutomationV1Routes(),
            ],
            [
                "routePrefix"      => RoutePrefixes::BASECAMP . '/v1',
                "registeredRoutes" => self::BASECAMP_V1_ROUTES,
                "ourRoutes"        => $this->ourBasecampV1Routes(),
            ],
        ];
    }

    /**
     * @test
     *
     * @param string $routeGroup
     * @param string $uri
     * @param array  $methods
     *
     * @dataProvider routesProvider
     */
    public function intended_routes_should_be_registered(string $routeGroup, string $uri, array $methods)
    {
        $this->assertTrue($this->groupedRegisteredRoutes->get($routeGroup)->has($uri),
            "Route $uri for group $routeGroup does not exist");
        /** @var Collection $actualMethods */
        $actualMethods = $this->groupedRegisteredRoutes->get($routeGroup)->get($uri);
        foreach ($methods as $method) {
            $this->assertTrue($actualMethods->has($method), "Path: $uri. Method $method does not exist.");
        }
    }

    public function routesProvider(): array
    {
        $v3Routes           = $this->ourV3Routes()->map(fn(Collection $methods, string $uri) => [
            self::V3_ROUTES,
            $uri,
            $methods->toArray(),
        ])->values()->toArray();
        $v4Routes           = $this->ourV4Routes()->map(fn(Collection $methods, string $uri) => [
            self::V4_ROUTES,
            $uri,
            $methods->toArray(),
        ])->values()->toArray();
        $liveV1Routes       = $this->ourLiveV1Routes()->map(fn(Collection $methods, string $uri) => [
            self::LIVE_V1_ROUTES,
            $uri,
            $methods->toArray(),
        ])->values()->toArray();
        $automationV1Routes = $this->ourAutomationV1Routes()->map(fn(Collection $methods, string $uri) => [
            self::AUTOMATION_V1_ROUTES,
            $uri,
            $methods->toArray(),
        ])->values()->toArray();
        $webhookV1Routes    = $this->ourWebhookV1Routes()->map(fn(Collection $methods, string $uri) => [
            self::WEBHOOK_V1_ROUTES,
            $uri,
            $methods->toArray(),
        ])->values()->toArray();
        $basecampV1Routes   = $this->ourBasecampV1Routes()->map(fn(Collection $methods, string $uri) => [
            self::BASECAMP_V1_ROUTES,
            $uri,
            $methods->toArray(),
        ])->values()->toArray();

        return array_merge($v3Routes, $v4Routes, $liveV1Routes, $automationV1Routes, $webhookV1Routes,
            $basecampV1Routes);
    }
}
