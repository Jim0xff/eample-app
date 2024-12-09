<?php

namespace App\Http\Controllers;

use App\Adapters\LoginUser;
use Illuminate\Http\Request;
use Pump\User\Request\CreateUserRequest;
use Pump\User\Services\UserService;

class UserController extends Controller
{
   public function createUser(Request $request)
   {
       $params = $request->all();
       $createRequest = new CreateUserRequest();
       $createRequest->address = strtolower($params['address']);
       if(!empty($params['walletType'])){
           $createRequest->walletType = ($params['walletType']);
       }
       if(!empty($params['nickName'])){
           $createRequest->nickName = ($params['nickName']);
       }
       if(!empty($params['headImgUrl'])){
           $createRequest->headImgUrl = ($params['headImgUrl']);
       }
       if(!empty($params['website'])){
           $createRequest->website = ($params['website']);
       }
       if(!empty($params['telegramLink'])){
           $createRequest->telegramLink = ($params['telegramLink']);
       }
       if(!empty($params['twitterLink'])){
           $createRequest->twitterLink = ($params['twitterLink']);
       }
       /** @var $userService UserService */
       $userService =  resolve('user_service');
       $token = $userService->createUser($createRequest);
       return response()->json(['data' => ['token'=>$token], 'code' => 200]);
   }

   public function editUser(Request $request)
   {
       $params = $request->all();
       /** @var $userService UserService */
       $userService =  resolve('user_service');
       /** @var LoginUser $user */
       $user = auth()->user();
       $params['user'] =  $user->address;
       $userService->editUser($params);
       return response()->json(['data' => true, 'code' => 200]);

   }

   public function followUser(Request $request)
   {
       $params = $request->all();
       /** @var LoginUser $user */
       $user = auth()->user();
       $params['user'] =  $user->address;
       /** @var $userService UserService */
       $userService =  resolve('user_service');
       $userService->followUser($params);
       return response()->json(['data' => true, 'code' => 200]);
   }

    public function cancelFollowUser(Request $request)
    {
        $params = $request->all();
        /** @var LoginUser $user */
        $user = auth()->user();
        $params['user'] =  $user->address;
        /** @var $userService UserService */
        $userService =  resolve('user_service');
        $userService->cancelFollow($params);
        return response()->json(['data' => true, 'code' => 200]);
    }

   public function getUser(Request $request){
       /** @var $userService UserService */
       $userService =  resolve('user_service');
       $params = $request->all();
       $user = auth()->user();
       $loginUserAddress = null;
       if(!empty($user)){
           $loginUserAddress = $user->address;
       }
       $user = $userService->getSingleUserDTO($params['address'], $loginUserAddress);
       return response()->json(['data' => $user, 'code' => 200]);
   }

   public function followerList(Request $request)
   {
       $params = $request->all();
       /** @var $userService UserService */
       $userService =  resolve('user_service');
       $data = $userService->followerList($params);
       return response()->json(['data' => $data, 'code' => 200]);
   }

   public function followingList(Request $request)
   {
       $params = $request->all();
       /** @var $userService UserService */
       $userService =  resolve('user_service');
       $data = $userService->followingList($params);
       return response()->json(['data' => $data, 'code' => 200]);
   }
}
