<?php

namespace Pump\User\Providers;

use App\Adapters\TokenAuthorizedProvider;
use Illuminate\Support\Facades\Auth;

class AuthServiceProvider extends \Illuminate\Foundation\Support\Providers\AuthServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [

    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Auth::provider('api_token', function () {
            return new TokenAuthorizedProvider();
        });
    }
}
