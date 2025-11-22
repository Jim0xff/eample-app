<?php

namespace Pump\Token\Service;

use App\InternalServices\Airdrop\AirdropService;

class ServiceFeeService
{
    public function getServiceFeeAmount($user)
    {
        /** @var AirdropService $airdropService */
        $airdropService = resolve('airdrop_service');

        $rawRt = $airdropService->airdropGet("get-service-fee-amount", [
            "address"=>$user->address
        ],
            ["apiToken"=>config("internal.airdrop_service_api_key")], false);

        return $rawRt['totalAmount'];
    }

    public function getServiceFeeAmountTotal($user)
    {
        /** @var AirdropService $airdropService */
        $airdropService = resolve('airdrop_service');

        $rawRt = $airdropService->airdropGet("get-service-fee-amount-total", [
            "address"=>$user->address
        ],
            ["apiToken"=>config("internal.airdrop_service_api_key")], false);
        return $rawRt['totalAmount'];
    }

    public function serviceFeePermit($user)
    {
        /** @var AirdropService $airdropService */
        $airdropService = resolve('airdrop_service');

        $rawRt = $airdropService->airdropGet("service-fee-permit", [
            "address"=>$user->address
        ],
            ["apiToken"=>config("internal.airdrop_service_api_key")], false);
        return $rawRt;
    }

    public function cancelServiceFee($serviceFeeIds)
    {
        /** @var AirdropService $airdropService */
        $airdropService = resolve('airdrop_service');

        $airdropService->airdropPost("cancel-service-fee", [
            "serviceFeeIds"=>$serviceFeeIds
        ],
            ["apiToken"=>config("internal.airdrop_service_api_key")], false);
    }

    public function getServiceFeeRecord($user, $pageNum, $pageSize)
    {
        /** @var AirdropService $airdropService */
        $airdropService = resolve('airdrop_service');

        $rawRt = $airdropService->airdropGet("get-user-service-fee-record", [
            "address"=>$user->address,
            "page" => $pageNum,
            "pageSize" => $pageSize
        ],
            ["apiToken"=>config("internal.airdrop_service_api_key")], false);

        if(!empty($rawRt["items"])){
            foreach($rawRt["items"] as &$item){
                $tokenInfo = [
                    "address" => $item['token'],
                    "decimals" => "18",
                    "name" => "metis",
                    "symbol" => "metis"
                ];
                $item["tokenInfo"] = $tokenInfo;
            }
        }
        return [
            "items" => $rawRt["items"],
            "pagination" => [
                "total" => $rawRt['pagination']["total"],
                "pageNum" => $rawRt['pagination']["page"],
                "pageSize" => $rawRt['pagination']["pageSize"],
            ]
        ];
    }
}
