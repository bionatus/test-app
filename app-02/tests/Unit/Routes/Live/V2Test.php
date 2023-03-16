<?php

namespace Tests\Unit\Routes\Live;

use App\Constants\RouteParameters;
use App\Constants\RoutePrefixes;
use Illuminate\Routing\Route;
use Illuminate\Support\Collection;
use Str;
use Tests\TestCase;

class V2Test extends TestCase
{
    private Collection $groupedRegisteredRoutes;

    public function setUp(): void
    {
        parent::setUp();
        $routes       = Collection::make(\Route::getRoutes()->getRoutes());
        $liveV2Routes = $routes->filter(function(Route $a) {
            return Str::startsWith($a->uri(), RoutePrefixes::LIVE . '/v2');
        });

        $this->groupedRegisteredRoutes = Collection::make();
        $liveV2Routes->each(function(Route $route) {
            $uri     = Str::substr($route->uri(), Str::length(RoutePrefixes::LIVE . '/v2/'));
            $methods = Collection::make($this->groupedRegisteredRoutes[$uri] ?? []);
            foreach ($route->methods() as $method) {
                if (in_array($method, ['HEAD', 'OPTIONS'])) {
                    continue;
                }
                $methods->put($method, $method);
            }
            $this->groupedRegisteredRoutes->put($uri, $methods);
        });
    }

    /** @test */
    public function registered_routes_should_be_tested()
    {
        $failed    = Collection::make();
        $ourRoutes = $this->ourLiveV2Routes();

        $this->groupedRegisteredRoutes->each(function(Collection $methods, string $uri) use (
            $ourRoutes,
            $failed
        ) {
            $routePrefix = RoutePrefixes::LIVE . '/v2';
            if (!$ourRoutes->has($uri)) {
                $failed->push("Failed asserting that $routePrefix/$uri is tested.");

                return;
            }

            /** @var Collection $ourMethods */
            $ourMethods = $ourRoutes->get($uri);
            $methods->each(function(string $method) use ($uri, $ourMethods, $failed, $routePrefix) {
                if (!$ourMethods->contains($method)) {
                    $failed->push("Failed asserting that $routePrefix/$uri has method $method.");
                }
            });
        });

        $messages = $failed->implode("\n");
        $this->assertEmpty($failed, $messages);
    }

    private function ourLiveV2Routes(): Collection
    {
        $appSetting           = RouteParameters::APP_SETTING;
        $brand                = RouteParameters::BRAND;
        $channel              = RouteParameters::CHANNEL;
        $country              = RouteParameters::COUNTRY;
        $customItem           = RouteParameters::SUPPLIER_CUSTOM_ITEM_ITEM_ORDER;
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
            'orders'                                                     => Collection::make(['GET']),
            'orders/in-progress/{' . $order . '}/cancel'                 => Collection::make(['POST']),
            'orders/in-progress/{' . $order . '}/extra-items'            => Collection::make(['GET', 'PATCH']),
            'orders/{' . $order . '}'                                    => Collection::make(['GET', 'PATCH']),
            'orders/{' . $order . '}/assignment'                         => Collection::make(['POST']),
            'orders/{' . $order . '}/complete'                           => Collection::make(['POST']),
            'orders/{' . $order . '}/cancel'                             => Collection::make(['POST']),
            'orders/{' . $order . '}/custom-items'                       => Collection::make(['GET', 'POST']),
            'orders/{' . $order . '}/custom-items/{' . $customItem . '}' => Collection::make(['DELETE']),
            'orders/{' . $order . '}/extra-items'                        => Collection::make(['GET', 'PATCH']),
            'orders/{' . $order . '}/invoice'                            => Collection::make(['POST', 'DELETE']),
            'orders/{' . $order . '}/send-for-approval'                  => Collection::make(['POST']),
            'orders/{' . $order . '}/parts'                              => Collection::make(['GET']),
            'orders/{' . $order . '}/parts/{' . $partItemOrder . '}'     => Collection::make(['GET', 'PATCH']),
            'parts/{' . $part . '}'                                      => Collection::make(['GET']),
            'supplier'                                                   => Collection::make(['GET', 'PATCH']),
            'supplier/users'                                             => Collection::make(['GET']),
            'supplier/staff'                                             => Collection::make(['GET']),

            'orders/{' . $order . '}/parts/{' . $partItemOrder . '}/replacements' => Collection::make(['GET']),

            /* |--- FALLBACK TO V1 ---| */

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
            'orders/in-progress/{' . $order . '}/delivery'          => Collection::make(['PATCH']),
            'orders/in-progress/{' . $order . '}/items'             => Collection::make(['GET']),
            'orders/{' . $order . '}/delivery'                      => Collection::make(['PATCH']),
            'orders/{' . $order . '}/delivery/eta'                  => Collection::make(['PATCH']),
            'orders/{' . $order . '}/fees'                          => Collection::make(['POST']),
            'orders/{' . $order . '}/pre-approve'                   => Collection::make(['POST']),
            'orders/{' . $order . '}/reopen'                        => Collection::make(['POST']),
            'parts'                                                 => Collection::make(['GET']),
            'parts/{' . $part . '}/recommended-replacements'        => Collection::make(['POST']),
            'parts/{' . $part . '}/replacements'                    => Collection::make(['GET']),
            'removed-users'                                         => Collection::make(['GET']),
            'removed-users/{' . $user . '}'                         => Collection::make(['POST', 'DELETE']),
            'settings'                                              => Collection::make(['GET']),
            'settings/{' . $settingSupplier . '}'                   => Collection::make(['GET']),
            'settings/bulk-notification'                            => Collection::make(['POST']),
            'supplier/bulk-brand'                                   => Collection::make(['POST']),
            'supplier/bulk-hour'                                    => Collection::make(['POST']),
            'supplier/users/{' . $channel . '}'                     => Collection::make(['GET']),
            'users'                                                 => Collection::make(['GET']),
            'users/{' . $user . '}/confirm'                         => Collection::make(['POST']),
            'users/{' . $user . '}/new-message'                     => Collection::make(['POST']),
            'users/{' . $user . '}/orders'                          => Collection::make(['GET']),
            'confirmed-users/{user}'                                => Collection::make(['PATCH']),

            'orders/in-progress/{' . $order . '}/items/{' . $itemOrder . '}/remove'      => Collection::make(['POST']),
            'orders/in-progress/{' . $order . '}/delivery/curri/calculate-price'         => Collection::make(['POST']),
            'orders/in-progress/{' . $order . '}/delivery/curri/confirm'                 => Collection::make(['POST']),
            'orders/in-progress/{' . $order . '}/delivery/curri/notice/en-route/confirm' => Collection::make(['POST']),
            'unauthenticated/orders/{' . $unauthenticatedOrder . '}'                     => Collection::make(['GET']),
            'unauthenticated/orders/{' . $unauthenticatedOrder . '}/approve'             => Collection::make(['POST']),
            'unauthenticated/orders/{' . $unauthenticatedOrder . '}/items'               => Collection::make(['GET']),
        ]);
    }

    /**
     * @test
     */
    public function intended_routes_should_be_registered()
    {
        $this->ourLiveV2Routes()->each(function($methods, $uri) {
            $this->assertTrue($this->groupedRegisteredRoutes->has($uri),
                "Route $uri for group liveV2Routes does not exist");

            /** @var Collection $actualMethods */
            $actualMethods = $this->groupedRegisteredRoutes->get($uri);
            foreach ($methods as $method) {
                $this->assertTrue($actualMethods->has($method), "Path: $uri. Method $method does not exist.");
            }
        });
    }
}
