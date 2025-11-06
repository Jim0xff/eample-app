<?php

namespace Brick\PessimisticLocking;

use Brick\PessimisticLocking\Drivers\RedisDriver;

class PessimisticLockingProvider extends \Illuminate\Support\ServiceProvider
{


    public function register()
    {
        $this->app->singleton(PessimisticLocking::class, function ($app) {
            $driver = new RedisDriver($app['redis']);
            return new PessimisticLocking($driver);
        });
        $this->app->alias(PessimisticLocking::class, "locker");

    }

}
