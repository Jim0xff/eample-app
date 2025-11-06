<?php

namespace App\Adapters;


use App\InternalServices\LazpadTaskService\LazpadTaskService;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Pump\User\Request\CreateUserRequest;
use Pump\User\Services\LoginToken;
use Pump\User\Services\UserService;

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

//        /** @var $loginTokenService LoginToken */
//        $loginTokenService = resolve('login_token_service');
        /** @var $taskPointService LazpadTaskService */
        $taskPointService = resolve('lazpad_task_service');
        $rt = $taskPointService->decodeToken($token);
        $createRequest = $this->generateUserCreateRequest($rt);
        /** @var $userService UserService */
        $userService = resolve('user_service');
        $userService->createUserNotLogin($createRequest);

        if(empty($rt)){
            return null;
        }
        return new LoginUser($rt);
    }

    private function generateUserCreateRequest($user)
    {
        $request = new CreateUserRequest();
        $request->address = $user['address'];
        $request->walletType = "MetaMask";
        $request->nickName = $user['name'];
        $request->twitterLink = $user['content']??['twitterUserInfo']??['name'];
        $request->telegramLink = $user['content']??['tgUserInfo']??['id'];
        return $request;
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
