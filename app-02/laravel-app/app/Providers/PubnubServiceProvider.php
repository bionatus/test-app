<?php

namespace App\Providers;

use Config;
use Illuminate\Support\ServiceProvider;
use PubNub\PNConfiguration;
use PubNub\PubNub;

class PubnubServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(PubNub::class, function($app, $params) {
            $publishKey   = Config::get('pubnub.publish_key');
            $secretKey    = Config::get('pubnub.secret_key');
            $subscribeKey = Config::get('pubnub.subscribe_key');
            $uuid         = array_key_exists('uuid', $params) ? $params['uuid'] : Config::get('pubnub.uuid');

            $PNConfiguration = new PNConfiguration();
            $PNConfiguration->setPublishKey($publishKey);
            $PNConfiguration->setSecretKey($secretKey);
            $PNConfiguration->setSubscribeKey($subscribeKey);
            $PNConfiguration->setUuid($uuid);

            return new PubNub($PNConfiguration);
        });
    }
}
