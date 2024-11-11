<?php

namespace App\Http\Controllers;
use phpseclib\Math\BigInteger;
use Web3\Formatters\AddressFormatter;
use Web3\Utils;
use Web3\Web3;
use Web3\ValueObjects\{Transaction, Wei};
use Web3\Contract;
use Web3\Providers\HttpAsyncProvider;
use function React\Async\await;


class TestController extends Controller{


    public function test1(string $id)
    {
        $abiObj = config("abi.TokenFactory");

        $web3 = new Web3(new HttpAsyncProvider('https://sepolia.metisdevops.link'),30);

        $contract = new Contract($web3->provider, $abiObj);
        $tokenPrice = 0;
        $rt = $contract->at("0xe8385f3115f2aa17b1AB5B54508a41b834f7787b")->call("tokenCap","0x245212f8791ab253e8170bf65cf1c9d753cdd607",[], function($err, $result) use(&$tokenPrice) {

            if(!empty($result[0])){
                /** @var BigInteger $priceObj */
                $priceObj = $result[0];
                $tokenPrice = $priceObj->toString();
            }
            $functionResult = $result;
        });
//        $net = $web3->getNet();
//        $net->listening(function ($err, $result){
//            var_dump($result);
//        });

//        $logInfo = [];
//        $rt2 = $web3->getEth()->getLogs(["topics"=>[Utils::sha3("RaffleEnter(address,uint256,uint256)")]],function ($err, $result) use (&$logInfo){
//            $logInfo['content'] = json_decode(json_encode($result), true);
//
//            $address = AddressFormatter::format($result[0]->topics[1]);
//
//        });

        //0xAb02bbc8F7eE65e8F03014A9580071e1b439DbB3
        await($rt);
//        await($rt2);
        return response()->json(['code' => 200, 'data' => $tokenPrice]);
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

}
