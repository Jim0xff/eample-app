<?php

namespace App\Http\Controllers;

use App\Adapters\LoginUser;
use App\InternalServices\DomainException;
use Carbon\Carbon;
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
       $params['address'] = strtolower($params['address']);
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

   public function tradingList(Request $request)
   {
       $params = $request->all();
       /** @var $tokenService TokenService */
       $tokenService = resolve('token_service');
       return response()->json(['data' => $tokenService->tradingList($params), 'code' => 200]);
   }


    public function getConfig()
    {
        return response()->json([
                "supports_search" => true,
                "supports_group_request" => false,
                "supports_marks" => false,
                "supports_timescale_marks" => false,
                "supports_time" => true,
                "exchanges" => [
                    ["value" => "", "name" => "All Exchanges", "desc" => ""]
                ],
                "symbols_types" => [
                    ["name" => "All types", "value" => ""]
                ],
                "supported_resolutions" => ["1", "5", "15", "30", "60", "1D", "1W", "1M"]
            ]);
    }

    // 时间接口
    public function getTime()
    {
        return response()->json(Carbon::now()->timestamp);
    }

    public function getHistory(Request $request)
    {
        $params = $request->all();
        if(empty($request->get('symbol'))){
            throw new DomainException("symbol is required");
        }

        $resolution = $request->get('resolution', '60');
        $from = $request->get('from', Carbon::now()->subDay()->timestamp);
        $to = $request->get('to', Carbon::now()->timestamp);
        $params['resolution'] = $resolution;
        $params['from'] = $from;
        $params['to'] = $to;
        $params['symbol'] = strtolower($request->get('symbol'));
        /** @var $tokenService TokenService */
        $tokenService = resolve('token_service');
        $data = $tokenService->getTokenHistory($params);
        return response()->json($data);
    }

}
