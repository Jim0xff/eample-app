<?php

namespace App\Http\Controllers;
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
        $data = [
            "a" => 1,
            "b" => 2
        ];

        $abiObj = config("abi.Raffle");
        $web3 = new Web3(new HttpAsyncProvider('http://127.0.0.1:8545'),30);

        $contract = new Contract($web3->provider, $abiObj);
        $transaction = [
            "from" => "0xf39Fd6e51aad88F6F4ce6aB8827279cffFb92266",
            "value"=> 10 ** 17,

        ];
        $rt = $contract->at("0xAb02bbc8F7eE65e8F03014A9580071e1b439DbB3")->send("enterRaffle",$transaction, function($err, $result) {
            $transactionNo = $result;
            //var_dump($transactionNo);
        });
//        $net = $web3->getNet();
//        $net->listening(function ($err, $result){
//            var_dump($result);
//        });

        $logInfo = [];
        $rt2 = $web3->getEth()->getLogs(["topics"=>[Utils::sha3("RaffleEnter(address,uint256,uint256)")]],function ($err, $result) use (&$logInfo){
            $logInfo['content'] = json_decode(json_encode($result), true);

            $address = AddressFormatter::format($result[0]->topics[1]);

        });

        //0xAb02bbc8F7eE65e8F03014A9580071e1b439DbB3
        await($rt);
        await($rt2);
        return response()->json(['code' => 200, 'data' => $logInfo]);
    }



}
