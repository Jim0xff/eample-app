<?php

namespace App\InternalServices\CoBuildAgent;

use App\InternalServices\GuzzleClient;

class CoBuildAgentServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register()
    {
        $this->app->singleton(CoBuildAgentInternalService::class, function() {

            $client = new GuzzleClient(config("internal.co_build_agent_internal_service"));

            return new CoBuildAgentInternalService($client);
        });

        $this->app->alias(CoBuildAgentInternalService::class, "co_build_agent_internal_service");
    }
}
