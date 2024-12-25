<?php

namespace App\Http\Controllers;

use App\Adapters\LoginUser;
use App\InternalServices\DomainException;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Pump\Token\Service\TokenService;
use Pump\Utils\LogUtils;

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

   public function topOfTheMoon(Request $request)
   {
       $params = $request->all();
       /** @var $tokenService TokenService */
       $tokenService = resolve('token_service');
       /** @var LoginUser $user */
       $user = auth()->user();
       if(!empty($user)){
           $params['userId'] = $user->address;
       }
       return response()->json(['data' => $tokenService->topOfTheMoon($params), 'code' => 200]);
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
        return response()->json(['data'=>
            [
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
            ],
            'code' => 200
        ]);
    }

    public function resolveSymbol(Request $request)
    {
        $symbol = $request->query('symbol');

        // Example response for a symbol
        $data = [
            'name' => $symbol,
            'ticker' => $symbol,
            'description' => 'Description of ' . $symbol,
            'type' => 'crypto',
            'session' => '24x7',
            'exchange' => 'ExampleExchange',
            'listed_exchange' => 'ExampleExchange',
            'timezone' => 'UTC',
            'minmov' => 1,
            'pricescale' => 100,
            'has_intraday' => true,
            'has_daily' => true,
            'has_weekly_and_monthly' => true,
            'currency_code' => 'METIS',
        ];

        return response()->json($data);
    }

    public function searchSymbols(Request $request)
    {
        $query = $request->query('query'); // The search query (e.g., symbol or name)
        $type = $request->query('type'); // The type of symbol (optional)
        $exchange = $request->query('exchange'); // The exchange filter (optional)

        // Example data (replace with real database or API logic)
        $symbols = [
            [
                'symbol' => $query,
                'full_name' => 'Bitcoin / US Dollar',
                'description' => 'Bitcoin vs US Dollar',
                'exchange' => 'ExampleExchange',
                'type' => 'crypto',
            ],
//            [
//                'symbol' => 'ETHUSD',
//                'full_name' => 'Ethereum / US Dollar',
//                'description' => 'Ethereum vs US Dollar',
//                'exchange' => 'ExampleExchange',
//                'type' => 'crypto',
//            ],
//            [
//                'symbol' => 'AAPL',
//                'full_name' => 'Apple Inc.',
//                'description' => 'Apple Inc.',
//                'exchange' => 'NASDAQ',
//                'type' => 'stock',
//            ],
        ];

        // Return the filtered symbols
        return response()->json($symbols);
    }

    public function getConfigPure()
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
            "supported_resolutions" => ["1", "5", "30", "60", "30M", "1H", "1D","1W"]
        ],);
    }

    // 时间接口
    public function getTime()
    {
        return response()->json(Carbon::now()->timestamp);
    }

    // 时间接口
    public function getTimePure()
    {
        return response()->json(Carbon::now()->timestamp);
    }

    public function getHistory(Request $request)
    {
        return LogUtils::process($request, function () use ($request) {
            if(empty($request->get('symbol'))){
                throw new DomainException("symbol is required");
            }
            $params = $request->all();
            $resolution = $request->get('resolution', '60');
            $from = $request->get('from', Carbon::now()->subDay()->timestamp);
            $to = $request->get('to', Carbon::now()->timestamp);
            $this->getFromToByResolution($resolution, $to, $from);
            $params['resolution'] = $resolution;
            $params['from'] = $from;
            $params['to'] = $to;
            $params['symbol'] = strtolower($request->get('symbol'));
            /** @var $tokenService TokenService */
            $tokenService = resolve('token_service');
            $data = $tokenService->getTokenHistory($params);
            return response()->json(['data'=>$data, 'code' => 200]);
        }) ;
    }

    public function getHistoryPure(Request $request)
    {
        return LogUtils::process($request, function () use ($request) {
            if(empty($request->get('symbol'))){
                throw new DomainException("symbol is required");
            }
            $params = $request->all();
            $resolution = $request->get('resolution', '60');
            $from = $request->get('from', Carbon::now()->subDay()->timestamp);
            $to = $request->get('to', Carbon::now()->timestamp);
            $this->getFromToByResolution($resolution, $to, $from);
            $params['resolution'] = $resolution;
            $params['from'] = $from;
            $params['to'] = $to;
            $params['symbol'] = strtolower($request->get('symbol'));
            /** @var $tokenService TokenService */
            $tokenService = resolve('token_service');
            $data = $tokenService->getTokenHistory($params);
            return response()->json($data);
        }) ;
    }


    private function getFromToByResolution($resolution, $to, &$from)
    {
        switch ($resolution){
            case "1":
                //1秒钟 不能查超过一小时的数据，否则可能出现性能问题
                if($to - $from > 3600){
                    $from = Carbon::createFromTimestamp($to)->subHours(1)->timestamp;
                }
                break;
            case "5":
                //5秒钟 不能查超过5小时的数据，否则可能出现性能问题
                if($to - $from > 5 * 3600){
                    $from = Carbon::createFromTimestamp($to)->subHours(5)->timestamp;
                }
                break;
            case "30":
                //30秒钟 不能查超过3天的数据，否则可能出现性能问题
                if($to - $from > 3 * 24 * 3600){
                    $from = Carbon::createFromTimestamp($to)->subHours(72)->timestamp;
                }
                break;
            case "60":
                //60秒钟 不能查超过7天的数据，否则可能出现性能问题
                if($to - $from > 7 * 24 * 3600){
                    $from = Carbon::createFromTimestamp($to)->subHours(24 * 7)->timestamp;
                }
                $from = Carbon::createFromTimestamp($to)->subHours(24 * 7)->timestamp;

                break;
            case "30M":
                //30分钟 不能查超过30天的数据，否则可能出现性能问题
                if($to - $from > 30 * 24 * 3600){
                    $from = Carbon::createFromTimestamp($to)->subDays(30)->timestamp;
                }
                break;
            case "1H":
                //60分钟 不能查超过80天的数据，否则可能出现性能问题
                if($to - $from > 80 * 24 * 3600){
                    $from = Carbon::createFromTimestamp($to)->subDays(80)->timestamp;
                }
                break;
            case "1D":
                //1天 不能查超过3年的数据，否则可能出现性能问题
                if($to - $from > 3 * 365 * 24 * 3600){
                    $from = Carbon::createFromTimestamp($to)->subyears(3)->timestamp;
                }
                break;
        }
    }

    public function getHistoryMock(Request $request)
    {
        $symbol = $request->get('symbol', 'BTC/USDT');
        $resolution = $request->get('resolution', '1D');
        $from = $request->get('from', Carbon::now()->subMonths(4)->timestamp);
        $to = $request->get('to', Carbon::now()->timestamp);

        // 模拟历史数据
        $data = [
            "t" => [], // 时间戳
            "o" => [], // 开盘价
            "h" => [], // 最高价
            "l" => [], // 最低价
            "c" => [], // 收盘价
            "v" => []  // 成交量
        ];
        $timeAdd = 0;
        switch ($resolution) {
            case '1':
                $timeAdd = 60;
                break;
            case '30':
                $timeAdd = 1800;
                break;
            case '60':
                $timeAdd = 3600;
                break;
            case '7D':
                $timeAdd = 86400*7;
                break;
            default:
                $timeAdd = 86400;
        }
        for ($i = $from; $i <= $to; $i += $timeAdd) { // 每日一根K线
            $open = rand(30000, 40000);
            $high = $open + rand(0, 1000);
            $low = $open - rand(0, 1000);
            $close = rand($low, $high);
            $volume = rand(100, 1000);

            $data["t"][] = $i;
            $data["o"][] = $open;
            $data["h"][] = $high;
            $data["l"][] = $low;
            $data["c"][] = $close;
            $data["v"][] = $volume;
        }

        return response()->json(['data'=>[
            "s" => "ok", // 状态
            "t" => $data["t"],
            "o" => $data["o"],
            "h" => $data["h"],
            "l" => $data["l"],
            "c" => $data["c"],
            "v" => $data["v"]
        ], "code" => 200]);
    }

}
