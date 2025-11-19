<?php

namespace App\Http\Controllers;
use App\InternalServices\Coingecko\CoingeckoService;
use App\InternalServices\OpenLaunchChatService\OpenLaunchChatService;
use Aws\Result;
use Aws\S3\S3Client;
use Blockchainethdev\EthereumTx\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use phpseclib\Math\BigInteger;
use Web3\Formatters\AddressFormatter;
use Web3\Methods\Eth\Accounts;
use Web3\Utils;
use Web3\Web3;
use Web3\Contract;
use Web3\Providers\HttpAsyncProvider;
use function React\Async\await;
use Illuminate\Support\Facades\Log;


class TestController extends Controller{

    function contract_call($from_address,$contract_address,$callData, $privateKey){
        $web3 = new Web3(new HttpAsyncProvider('https://sepolia.metisdevops.link'),false);

        $Eth = $web3->getEth();
        $transaction_dict =[
            'from' => $from_address,
            'to' => $contract_address,
            'data' => $callData
        ];
        $Eth->getTransactionCount($from_address, function ($err, $transactionCount)use($contract_address,$callData,$Eth,$transaction_dict,$privateKey) {
            if ($err !== null) {
                //print_r($err);
            }
            $nonce = $transactionCount->toString();
            $transaction_dict['nonce'] = intval($nonce);
            $Eth->gasPrice(function ($err, $gasPrice)use($nonce,$contract_address,$callData,$Eth,$transaction_dict,$privateKey) {
                $Eth->estimateGas($transaction_dict, function ($err, $gas)use($gasPrice,$Eth,$transaction_dict,$privateKey) {
                    if ($err !== null) {
                        Log::error("estimateGasError:$err");
                        exit(1);
                    }
                    $transaction_dict['gasPrice'] =intval($gasPrice->toString());
                    $transaction_dict["gas"] = intval($gas->toString());
                    $transaction_dict["chainId"] = '59902';
                    $transaction = new Transaction($transaction_dict);
                    $sign_data = $transaction->sign($privateKey);
                    $Eth->sendRawTransaction("0x".$sign_data, function ($err, $transaction) use ($Eth){
                        if ($err !== null) {
                            Log::error("sendRawTransactionError:$err");
                            exit(1);
                        }
                        echo 'tx: ' . $transaction . PHP_EOL;
                    });
                });
            });
        });
    }

    public function test1(string $id)
    {
        $abiObj = config("abi.TokenFactory");

        $web3 = new Web3(new HttpAsyncProvider('https://sepolia.metisdevops.link'),false);
//        $ee = ($web3->getPersonal()->listAccounts(function ($err, $result){
//            var_dump($result);
//        }));
//        await($ee);
//        exit;
        $contract = new Contract($web3->provider, $abiObj);

        $functionResult = null;
        $callData = $contract->at("0xe8385f3115f2aa17b1AB5B54508a41b834f7787b")->getData("createToken",
            "PTXEEETBB",
            "PTX2",
            "descdesc3",
            "https://pump.sgp1.digitaloceanspaces.com/tokenImg_0xd4f8bbf9c0b8aff6d76d2c5fa4971a36fc9e4003_aR9CMa9QbX",
            '0',
            '0xDeadDeAddeAddEAddeadDEaDDEAdDeaDDeAD0000'
        );

        //$rry = $this->contract_call(strtolower("0xd4F8bbF9c0B8AFF6D76d2C5Fa4971a36fC9e4003"),"0xe8385f3115f2aa17b1AB5B54508a41b834f7787b", "0x".$callData, "ccc61019e6a49bcc428812c80c8ddebeebbe8530213d2ac4699909248cf32e92");
        $rrte = $web3->getEth()->getTransactionReceipt("0x02a20bdc6492edda34b835fe9d8f877575a0a13d308c0226400862374595a334", function ($err, $transactionReceipt) {
            print_r(json_encode($transactionReceipt));
        });
        await($rrte);
        exit;
        $rt = $contract->at("0xe8385f3115f2aa17b1AB5B54508a41b834f7787b")->send("createToken",
            "PTXEEETAA",
            "PTX1",
            "descdesc2",
            "https://pump.sgp1.digitaloceanspaces.com/tokenImg_0xd4f8bbf9c0b8aff6d76d2c5fa4971a36fc9e4003_aR9CMa9QbX",
            '0',
            '0xDeadDeAddeAddEAddeadDEaDDEAdDeaDDeAD0000'
            ,[], function($err, $result) use(&$functionResult) {
            Log::info("ghpp:$err");
            $functionResult = $result;
        });
        await($rt);
//        $net = $web3->getNet();
//        $net->listening(function ($err, $result){
//            var_dump($result);
//        });
//
//        $logInfo = [];
//        $rt2 = $web3->getEth()->getLogs(["topics"=>[Utils::sha3("TokenCreated(indexed address,string,string,indexed address,string,string,uint256,uint256,address)")]],function ($err, $result) use (&$logInfo){
//            $logInfo['content'] = json_decode(json_encode($result), true);
//
//            $address = AddressFormatter::format($result[0]->topics[1]);
//
//        });
//
//        //0xAb02bbc8F7eE65e8F03014A9580071e1b439DbB3
//
//        await($rt2);
        return response()->json(['code' => 200, 'data' => $functionResult]);
    }

    public function test88()
    {
        $web3 = new Web3(new HttpAsyncProvider('https://sepolia.metisdevops.link'),false);
        $net = $web3->getNet();
        $net->listening(function ($err, $result){
            var_dump($result);
        });

        $logInfo = [];
        $rt2 = $web3->getEth()->getLogs([
            "topics"=>[
            Utils::sha3("TokenCreated(address,string,string,address,string,string,uint256,uint256,address)")
            ],
            "address"=>"0xe8385f3115f2aa17b1AB5B54508a41b834f7787b"
        ],function ($err, $result) use (&$logInfo){
            $logInfo['content'] = json_decode(json_encode($result), true);
            //$address = AddressFormatter::format($result[0]->topics[1]);
        });

        //0xAb02bbc8F7eE65e8F03014A9580071e1b439DbB3

        await($rt2);
        print_r($logInfo);
    }


    public function test2()
    {
        $abiObj = config("abi.NetswapPair");

        $web3 = new Web3(new HttpAsyncProvider('https://sepolia.metisdevops.link'),30);

        $contract = new Contract($web3->provider, $abiObj);
        $tokenPrice = 0;
        $functionResult = [];
        $rt = $contract->at("0x73902A13c97AFB4e6F62Ea0382C5BC323E734E6A")->call("getReserves",[], function($err, $result) use(&$functionResult) {
            $functionResult = $result;
        });
        $token0Result = null;
        $token1Result = null;
        $rt2 = $contract->at("0x73902A13c97AFB4e6F62Ea0382C5BC323E734E6A")->call("token0",[], function($err, $result) use(&$token0Result) {
            $token0Result = $result;
        });
        $rt3 = $contract->at("0x73902A13c97AFB4e6F62Ea0382C5BC323E734E6A")->call("token1",[], function($err, $result) use(&$token1Result) {
            $token1Result = $result;
        });
        await($rt);
        await($rt2);
        await($rt3);
        $currencyToken = null;
        $memeToken = null;
        $currencyAmount = 0;
        $memeTokenAmount = 0;
        if(!empty($token1Result[0]) && !empty($token0Result[0]) && !empty($functionResult['_reserve0']) && !empty($functionResult['_reserve1'])){
            if(strtolower($token0Result[0]) == strtolower("0x73902A13c97AFB4e6F62Ea0382C5BC323E734E6A")){
                $currencyToken = $token0Result[0];
                $currencyAmount = $functionResult['_reserve0']->toString();
                $memeToken =   $token1Result[0];
                $memeTokenAmount = $functionResult['_reserve1']->toString();
            }else{
                $currencyToken = $token1Result[0];
                $currencyAmount = $functionResult['_reserve1']->toString();
                $memeToken = $token0Result[0];
                $memeTokenAmount = $functionResult['_reserve0']->toString();
            }
            $relativePrice = number_format($currencyAmount/$memeTokenAmount,5);
            print_r($relativePrice);
        }
        return response()->json(['code' => 200, 'data' => $tokenPrice]);
    }

    public function test3(Request $request)
    {
        //Create a S3Client
        //https://pump.sgp1.digitaloceanspaces.com
//        $client = new S3Client([
//            'version' => 'latest',
//            'region'  => 'us-east-1',
//            'endpoint' => 'https://sgp1.digitaloceanspaces.com',
//            'use_path_style_endpoint' => false, // Configures to use subdomain/virtual calling format.
//            'credentials' => [
//                'key'    => 'DO00X7XU744W9BRQBJ83',
//                'secret' => 'KDCZLUeRgOM1r/YEQMzoLZzFghhRrefGKFG/p9m/r78',
//            ],
//        ]);
//
////Listing all S3 Bucket
//        /** @var Result $result */
//        $result = $client->getObject([
//            'Key' => 'headImg_0xd4f8bbf9c0b8aff6d76d2c5fa4971a36fc9e4003_wDCrt88vAj',
//            'Bucket' => 'pump'
//        ]);
//        if(!empty($result->get('@metadata'))){
//            print_r($result);
//        }
//
//        $file = $request->file('img');
//
////        $result = $client->upload('pump','pug4.png', $file->get(), 'public-read');
////        if(!empty($result) && !empty($result->get('@metadata'))){
////            print_r($result->get('@metadata')['effectiveUri']);
////        }
        /** @var CoingeckoService $coingeckoService */
        $coingeckoService = resolve(CoingeckoService::class);
        $currencyInfo = $coingeckoService->getTokenPrice('metis-token', 'usd');

        return response()->json(['code' => $currencyInfo, 'data' => []]);
    }

    public function test4(Request $request){
//        $ccc = Carbon::createFromTimestamp(1731560817);
//        print_r($ccc->endOfHour());
//        $user = auth()->user();

//        /** @var OpenLaunchChatService $sss */
//        $sss = resolve('open_launch_chat_service');
//        //$rt = $sss->chatPost("co-build-agent/chat.json", ['app'=>'lazpad', 'outAgentId'=>'1','content'=>'Who is the next president of USA?'],[], true);

        /** @var CoingeckoService $coingeckoService */
        $coingeckoService = resolve(CoingeckoService::class);
        $currencyInfo = $coingeckoService->getTokenPrice('metis-token', 'usd');

        return response()->json(['code' => $currencyInfo, 'data' => true]);
    }

}
