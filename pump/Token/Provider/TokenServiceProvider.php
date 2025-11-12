<?php

namespace Pump\Token\Provider;

use Pump\Token\Service\CoBuildAgentService;
use Pump\Token\Service\TokenGraduateService;
use Pump\Token\Service\TokenService;
use Pump\Token\Service\TopOfTheMoonService;
use Pump\Token\Service\TradingService;

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

        $this->app->singleton(CoBuildAgentService::class, function() {
            return new CoBuildAgentService();
        });
        $this->app->alias(CoBuildAgentService::class, "co_build_agent_service");

        $this->app->singleton(TokenGraduateService::class, function() {
            return new TokenGraduateService();
        });
        $this->app->alias(TokenGraduateService::class, "token_graduate_service");

        $this->app->singleton(TradingService::class, function() {
            return new TradingService();
        });
        $this->app->alias(TradingService::class, "trading_service");

    }
}
