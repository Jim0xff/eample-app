<?php

namespace Pump\User\Providers;

use Pump\User\Services\LoginToken;
use Pump\User\Services\UserService;

class UserServiceProvider  extends \Illuminate\Support\ServiceProvider
{
    public function register()
    {
        $this->app->singleton(UserService::class, function() {
            return new UserService();
        });

        $this->app->singleton(LoginToken::class, function() {
            return new LoginToken();
        });

        $this->app->alias(UserService::class, "user_service");
        $this->app->alias(LoginToken::class, "login_token_service");

    }
}
