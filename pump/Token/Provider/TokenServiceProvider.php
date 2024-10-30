<?php

namespace Pump\Token\Provider;

use Pump\Token\Service\TokenService;

class TokenServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register()
    {
        $this->app->singleton(TokenService::class, function() {
            return new TokenService();
        });

        $this->app->alias(TokenService::class, "token_service");
    }
}
