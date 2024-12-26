<?php

namespace Pump\Token\Provider;

use Pump\Token\Service\TokenService;
use Pump\Token\Service\TopOfTheMoonService;

class TokenServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register()
    {
        $this->app->singleton(TokenService::class, function() {
            return new TokenService();
        });

        $this->app->alias(TokenService::class, "token_service");


        $this->app->singleton(TopOfTheMoonService::class, function() {
            return new TopOfTheMoonService();
        });

        $this->app->alias(TopOfTheMoonService::class, "top_of_the_moon_service");
    }
}
