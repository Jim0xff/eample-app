<?php

namespace App\InternalServices\OpenLaunchChatService;

use App\InternalServices\GuzzleClient;
use App\InternalServices\LazpadTaskService\LazpadTaskService;

class OpenLaunchChatServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register()
    {
        $this->app->singleton(OpenLaunchChatService::class, function() {

            $client = new GuzzleClient(config("internal.open-launch-chat-service"));

            return new OpenLaunchChatService($client);
        });

        $this->app->alias(OpenLaunchChatService::class, "open_launch_chat_service");
    }
}
