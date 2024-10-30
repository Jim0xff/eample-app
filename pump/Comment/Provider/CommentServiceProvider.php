<?php

namespace Pump\Comment\Provider;

use Illuminate\Support\ServiceProvider;
use Pump\Comment\Service\CommentService;

class CommentServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(CommentService::class, function() {
            return new CommentService();
        });

        $this->app->alias(CommentService::class, "comment_service");
    }
}
