<?php

namespace App\InternalServices\LazpadTaskService;

use App\InternalServices\GuzzleClient;

class LazpadTaskServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register()
    {
        $this->app->singleton(LazpadTaskService::class, function() {

            $client = new GuzzleClient(config("internal.task_point"));

            return new LazpadTaskService($client);
        });

        $this->app->alias(LazpadTaskService::class, "lazpad_task_service");
    }
}
