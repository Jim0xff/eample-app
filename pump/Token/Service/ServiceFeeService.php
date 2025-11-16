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

        return $rawRt;
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
