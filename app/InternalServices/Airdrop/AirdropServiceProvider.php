<?php

namespace App\InternalServices\CoBuildAgent;

use App\InternalServices\Airdrop\AirdropService;
use App\InternalServices\GuzzleClient;

class AirdropServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register()
    {
        $this->app->singleton(AirdropService::class, function() {

            $client = new GuzzleClient(config("internal.airdrop_service_url"));

            return new AirdropService($client);
        });

        $this->app->alias(AirdropService::class, "airdrop_service");
    }
}
