<?php

namespace App\Http\Controllers;

use App\Adapters\LoginUser;
use Illuminate\Http\Request;
use Pump\Token\Service\TokenService;

class TokenController extends Controller
{
   public function createToken(Request $request)
   {
       $params = $request->all();
       /** @var LoginUser $user */
       $user = auth()->user();
       $params['creator'] =  $user->address;
       /** @var $tokenService TokenService */
       $tokenService = resolve('token_service');

       $token = $tokenService->createToken($params);
       return response()->json(['data' => $token, 'code' => 200]);
   }

   public function tokenList(Request $request)
   {
       $params = $request->all();
       /** @var $tokenService TokenService */
       $tokenService = resolve('token_service');
       /** @var LoginUser $user */
       $user = auth()->user();
       if(!empty($user)){
           $params['userId'] = $user->address;
       }
       return response()->json(['data' => $tokenService->tokenList($params), 'code' => 200]);
   }

   public function userBoughtTokens(Request $request)
   {
       $params = $request->all();
       /** @var $tokenService TokenService */
       $tokenService = resolve('token_service');
       /** @var LoginUser $user */
       $user = auth()->user();
       return response()->json(['data' => $tokenService->userBoughtTokens($params), 'code' => 200]);
   }

   public function tokenDetail(Request $request)
   {
       $params = $request->all();
       /** @var $tokenService TokenService */
       $tokenService = resolve('token_service');
       /** @var LoginUser $user */
       $user = auth()->user();
       if(!empty($user)){
           $params['userId'] = $user->address;
       }
       return response()->json(['data' => $tokenService->tokenDetail($params), 'code' => 200]);
   }

   public function tokenHolder(Request $request)
   {
       $params = $request->all();
       /** @var $tokenService TokenService */
       $tokenService = resolve('token_service');
       return response()->json(['data' => $tokenService->tokenHolders($params), 'code' => 200]);
   }

   public function trandingList(Request $request)
   {
       $params = $request->all();
       /** @var $tokenService TokenService */
       $tokenService = resolve('token_service');
       return response()->json(['data' => $tokenService->tradingList($params), 'code' => 200]);
   }
}
