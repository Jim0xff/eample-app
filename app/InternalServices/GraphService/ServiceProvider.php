<?php

namespace App\InternalServices\GraphService;

use App\InternalServices\GuzzleClient;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
   public function register()
   {
       $this->app->singleton(Service::class, function() {

           $client = new GuzzleClient(config("internal.graph"));

           return new Service($client);
       });

       $this->app->alias(Service::class, "graph_service");
   }
}

