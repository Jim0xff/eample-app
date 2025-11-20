<?php

namespace App\InternalServices\Airdrop;

use App\InternalServices\AbstractService;
use App\InternalServices\DomainException;

class AirdropService extends AbstractService
{
    public function airdropGet($uri, $params, $headers, $needAuth = false){
        if(empty($headers)){
            $headers = [];
        }

        if($needAuth && empty(request()->header('Authorization'))){
            throw new DomainException("not auth" , "401");
        }
        if($needAuth && !empty(request()->header('Authorization'))){
            $headers['Authorization'] = request()->header('Authorization');
        }
        try{
            $headers['x-request-id'] = app('requestId');
        }catch (\Throwable $e){

        }

        $rtRaw = $this->getDataWithHeaders($uri, [
            "headers"=>$headers,
            "query"=>$params
        ]);

        if($rtRaw['code'] != 200){
            \Log::error(sprintf(
                "launch chat service get call failed, url: %s  params: %s  headers: %s  result: %s",
                $uri,
                json_encode($params, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                json_encode($headers, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                json_encode($rtRaw, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            ));
            throw new DomainException("open launch chat service call failed", $rtRaw['code']);
        }

        \Log::info(sprintf(
            "launch chat service get call result, url: %s  params: %s  headers: %s  result: %s",
            $uri,
            json_encode($params, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            json_encode($headers, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            json_encode($rtRaw, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        ));

        return $rtRaw['data'];
    }



    public function airdropPost($uri, $params, $headers, $needAuth = false){
        if(empty($headers)){
            $headers = [];
        }
        if($needAuth && empty(request()->header('Authorization'))){
            throw new DomainException("not auth" , "401");
        }
        if($needAuth && !empty(request()->header('Authorization'))){
            $headers['Authorization'] = request()->header('Authorization');
        }
        try{
            $headers['x-request-id'] = app('requestId');
        }catch (\Throwable $e){

        }

        $rtRaw = $this->postDataWithHeaders($uri, [
            "headers"=>$headers,
            "json"=>$params
        ]);


        if($rtRaw['code'] != 200){
            \Log::error(sprintf(
                "launch chat service post call failed, url: %s  params: %s  headers: %s  result: %s",
                $uri,
                json_encode($params, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                json_encode($headers, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                json_encode($rtRaw, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            ));
            throw new DomainException("open launch chat service call failed", $rtRaw['code']);
        }

        \Log::info(sprintf(
            "launch chat service get call result, url: %s  params: %s  headers: %s  result: %s",
            $uri,
            json_encode($params, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            json_encode($headers, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            json_encode($rtRaw, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        ));

        return $rtRaw['data'];
    }
}
