<?php

namespace App\InternalServices\Coingecko;

use App\InternalServices\GuzzleClient;

class CoingeckoServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register()
    {
        $this->app->singleton(CoingeckoService::class, function() {

            $client = new GuzzleClient(config("internal.coingecko"));

            return new CoingeckoService($client);
        });

        $this->app->alias(CoingeckoService::class, "coingecko_service");
    }
}
