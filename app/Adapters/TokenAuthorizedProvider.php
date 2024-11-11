<?php

namespace App\Adapters;


use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Pump\User\Services\LoginToken;

class TokenAuthorizedProvider implements UserProvider
{
    public function retrieveById($tokenArr)
    {

    }

    public function retrieveByToken($identifier, $token)
    {

    }

    public function updateRememberToken(Authenticatable $user, $token)
    {

    }

    public function retrieveByCredentials($tokenArr)
    {
        $token = array_shift($tokenArr);

        /** @var $loginTokenService LoginToken */
        $loginTokenService = resolve('login_token_service');
        $rt = $loginTokenService->validateToken($token);
        if(empty($rt)){
            return null;
        }
        return new LoginUser($rt);
    }

    /**
     * Validate a user against the given credentials.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable $user
     * @param  array $credentials
     * @return bool
     */
    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        return true;
    }

    public function rehashPasswordIfRequired(Authenticatable $user, #[\SensitiveParameter] array $credentials, bool $force = false)
    {
        // TODO: Implement rehashPasswordIfRequired() method.
    }
}
