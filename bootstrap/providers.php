<?php

return [
    App\Providers\AppServiceProvider::class,
    \App\InternalServices\GraphService\ServiceProvider::class,
    \Pump\User\Providers\UserServiceProvider::class,
    \Pump\Token\Provider\TokenServiceProvider::class,
    \Pump\Comment\Provider\CommentServiceProvider::class,
    Pump\User\Providers\AuthServiceProvider::class,

];
