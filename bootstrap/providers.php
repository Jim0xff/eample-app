<?php

return [
    App\Providers\AppServiceProvider::class,
    \App\InternalServices\GraphService\ServiceProvider::class,
    \Pump\User\Providers\UserServiceProvider::class,
    \Pump\Token\Provider\TokenServiceProvider::class,
    \Pump\Comment\Provider\CommentServiceProvider::class,
    Pump\User\Providers\AuthServiceProvider::class,
    \App\InternalServices\Coingecko\CoingeckoServiceProvider::class,
    MLL\GraphiQL\GraphiQLServiceProvider::class,
    \Nuwave\Lighthouse\LighthouseServiceProvider::class,
    \App\InternalServices\S3\S3ServiceProvider::class,
    \Brick\PessimisticLocking\PessimisticLockingProvider::class,
    \App\InternalServices\LazpadTaskService\LazpadTaskServiceProvider::class,
    \App\InternalServices\OpenLaunchChatService\OpenLaunchChatServiceProvider::class,
    \App\InternalServices\CoBuildAgent\CoBuildAgentServiceProvider::class,

];
