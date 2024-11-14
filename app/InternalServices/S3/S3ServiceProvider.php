<?php

namespace App\InternalServices\S3;

class S3ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register()
    {
        $this->app->singleton(S3Service::class, function() {
            return new S3Service();
        });

        $this->app->alias(S3Service::class, "s3_service");
    }
}
