<?php

namespace App\Providers;

use App\Constants\RouteParameters;
use App\Constants\RoutePrefixes;
use App\Http\Middleware\AcceptsJSON;
use App\Http\Middleware\LogSupplierApiUsage;
use App\Http\Middleware\LogUserApiUsage;
use App\Http\Middleware\ProvideLatamUser;
use App\Http\Middleware\ProvideLiveUser;
use App\Http\Middleware\SetRelationsMorphMap;
use App\Models\ItemOrder;
use App\Models\ItemOrder\Scopes\IsPart;
use App\Models\ItemOrder\Scopes\IsSupplierCustomItem;
use App\Models\Order;
use App\Models\Order\Scopes\ByLastSubstatuses;
use App\Models\Part;
use App\Models\Part\Scopes\ByParentRouteKey;
use App\Models\Phone;
use App\Models\Phone\Scopes\Assigned;
use App\Models\Phone\Scopes\ByFullNumber;
use App\Models\Phone\Scopes\Unverified;
use App\Models\Phone\Scopes\Verified;
use App\Models\Scopes\ByParent;
use App\Models\Scopes\ByRouteKey;
use App\Models\Scopes\ByUuid;
use App\Models\Setting;
use App\Models\Setting\Scopes\ByApplicableTo;
use App\Models\Substatus;
use App\Models\Supplier;
use App\Models\SupplyCategory;
use App\Models\SupportCallCategory;
use App\Models\Tag;
use App\Models\User;
use App\Types\TaggableType;
use Config;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use MenaraSolutions\Geographer\Country;
use MenaraSolutions\Geographer\Exceptions\ObjectNotFoundException;

class RouteServiceProvider extends ServiceProvider
{
    protected $namespace = 'App\Http\Controllers';

    public function boot()
    {
        Route::bind(RouteParameters::ASSIGNED_VERIFIED_PHONE, function($value) {
            return Phone::scoped(new ByFullNumber($value))
                ->scoped(new Verified())
                ->scoped(new Assigned())
                ->firstOrFail();
        });

        Route::bind(RouteParameters::COUNTRY, function($value) {
            try {
                $country = Country::build($value);
            } catch (ObjectNotFoundException $exception) {
                throw new ModelNotFoundException("No query results for Country $value");
            }

            return $country;
        });

        Route::bind(RouteParameters::PART, function($value) {
            return Part::scoped(new ByParentRouteKey($value))->firstOrFail();
        });

        Route::bind(RouteParameters::SETTING_SUPPLIER, function($value) {
            return Setting::scoped(new ByRouteKey($value))
                ->scoped(new ByApplicableTo(Supplier::MORPH_ALIAS))
                ->firstOrFail();
        });

        Route::bind(RouteParameters::SETTING_USER, function($value) {
            return Setting::scoped(new ByRouteKey($value))
                ->scoped(new ByApplicableTo(User::MORPH_ALIAS))
                ->firstOrFail();
        });

        Route::bind(RouteParameters::SUPPLY_CATEGORY, function($value) {
            return SupplyCategory::scoped(new ByRouteKey($value))->firstOrFail();
        });

        Route::bind(RouteParameters::SUPPORT_CALL_CATEGORY, function($value) {
            return SupportCallCategory::scoped(new ByRouteKey($value))->scoped(new ByParent())->firstOrFail();
        });

        Route::bind(RouteParameters::TAGGABLE, function($value) {
            $taggableType = Str::before($value, '-');
            $taggableId   = Str::after($value, '-');

            try {
                $tag      = new TaggableType(['id' => $taggableId, 'type' => $taggableType]);
                $taggable = $tag->taggable();
            } catch (Exception $exception) {
                $modelNotFound = new ModelNotFoundException();
                $modelNotFound->setModel(RouteParameters::TAGGABLE);
                throw $modelNotFound;
            }

            if (!$taggable) {
                throw (new ModelNotFoundException())->setModel(Tag::MORPH_MODEL_MAPS[$taggableType]);
            }

            return $taggable;
        });

        Route::bind(RouteParameters::UNAUTHENTICATED_ORDER, function($value) {
            return Order::scoped(new ByUuid($value))
                ->scoped(new ByLastSubstatuses(array_merge(Substatus::STATUSES_APPROVED, Substatus::STATUSES_CANCELED,
                    Substatus::STATUSES_COMPLETED, Substatus::STATUSES_PENDING_APPROVAL)))
                ->firstOrFail();
        });

        Route::bind(RouteParameters::UNVERIFIED_PHONE, function($value) {
            return Phone::scoped(new ByFullNumber($value))->scoped(new Unverified())->firstOrFail();
        });

        Route::bind(RouteParameters::SUPPLIER_CUSTOM_ITEM_ITEM_ORDER, function($value) {
            $parentKey = $this->getCurrentRoute()->parentOfParameter(RouteParameters::SUPPLIER_CUSTOM_ITEM_ITEM_ORDER);
            $parentName = array_search($parentKey, $this->getCurrentRoute()->parameters);
            if (RouteParameters::ORDER === $parentName) {
                $order = Order::query()->where(Order::routeKeyName(), $parentKey)->firstOrFail();
                return $order->itemOrders()->scoped(new ByUuid($value))->scoped(new IsSupplierCustomItem())->firstOrFail();
            }

            return ItemOrder::scoped(new ByUuid($value))->scoped(new IsSupplierCustomItem())->firstOrFail();
        });

        Route::bind(RouteParameters::PART_ITEM_ORDER, function($value) {
            $parentKey = $this->getCurrentRoute()->parentOfParameter(RouteParameters::PART_ITEM_ORDER);
            $parentName = array_search($parentKey, $this->getCurrentRoute()->parameters);
            if (RouteParameters::ORDER === $parentName) {
                $order = Order::query()->where(Order::routeKeyName(), $parentKey)->firstOrFail();
                return $order->itemOrders()->scoped(new ByUuid($value))->scoped(new IsPart())->firstOrFail();
            }

            return ItemOrder::scoped(new ByUuid($value))->scoped(new IsPart())->firstOrFail();
        });

        parent::boot();
    }

    public function map(Router $router)
    {
        $this->mapWebRoutes($router);

        $this->mapApiRoutes($router);

        $this->mapLatamApiRoutes($router);

        $this->mapLiveApiRoutes($router);

        $this->mapAutomationApiRoutes($router);

        $this->mapBasecampApiRoutes($router);

        $this->mapWebhookApiRoutes($router);
    }

    protected function mapWebRoutes(Router $router)
    {
        $router->middleware(['web', 'hasTeam'])->namespace($this->namespace)->group(base_path('routes/web.php'));
    }

    protected function mapApiRoutes(Router $router)
    {
        $router->prefix('api')->middleware([
            'api',
            LogUserApiUsage::class,
        ])->namespace($this->namespace)->group(base_path('routes/api.php'));
    }

    protected function mapLatamApiRoutes(Router $router)
    {
        $router->prefix(RoutePrefixes::API . '/v2')->middleware([
            AcceptsJSON::class,
            'api',
            SetRelationsMorphMap::class,
            ProvideLatamUser::class,
            LogUserApiUsage::class,
        ])->namespace($this->namespace)->group(base_path('routes/latam/v2.php'));
        $router->prefix(RoutePrefixes::API . '/v3')->middleware([
            AcceptsJSON::class,
            'api',
            SetRelationsMorphMap::class,
            ProvideLatamUser::class,
            LogUserApiUsage::class,
        ])->group(base_path('routes/latam/v3.php'));
        $router->prefix(RoutePrefixes::API . '/v4')->middleware([
            AcceptsJSON::class,
            'api',
            SetRelationsMorphMap::class,
            ProvideLatamUser::class,
            LogUserApiUsage::class,
        ])->group(base_path('routes/latam/v4.php'));

        $router->prefix(RoutePrefixes::API . '/nova')
            ->middleware(Config::get('nova.middleware', []))
            ->group(base_path('routes/latam/nova.php'));
    }

    protected function mapLiveApiRoutes(Router $router)
    {
        $router->prefix(RoutePrefixes::LIVE . '/v1')->middleware([
            AcceptsJSON::class,
            'api',
            SetRelationsMorphMap::class,
            ProvideLiveUser::class,
            LogSupplierApiUsage::class,
        ])->group(base_path('routes/live/v1.php'));

        $router->prefix(RoutePrefixes::LIVE . '/v2')->middleware([
            AcceptsJSON::class,
            'api',
            SetRelationsMorphMap::class,
            ProvideLiveUser::class,
            LogSupplierApiUsage::class,
        ])->group(base_path('routes/live/v2.php'));
    }

    protected function mapAutomationApiRoutes(Router $router)
    {
        $router->prefix(RoutePrefixes::AUTOMATION . '/v1')->middleware([
            AcceptsJSON::class,
            'api',
            SetRelationsMorphMap::class,
            ProvideLatamUser::class,
        ])->group(base_path('routes/automation/v1.php'));
    }

    protected function mapBasecampApiRoutes(Router $router)
    {
        $router->prefix(RoutePrefixes::BASECAMP . '/v1')->middleware([
            AcceptsJSON::class,
            'api',
            SetRelationsMorphMap::class,
            ProvideLatamUser::class,
        ])->group(base_path('routes/basecamp/v1.php'));
    }

    protected function mapWebhookApiRoutes(Router $router)
    {
        $router->prefix(RoutePrefixes::WEBHOOK . '/v1')->middleware([
            AcceptsJSON::class,
            'bindings',
            SetRelationsMorphMap::class,
            ProvideLatamUser::class,
        ])->group(base_path('routes/webhook/v1.php'));
    }
}
