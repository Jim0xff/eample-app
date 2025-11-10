<?php

namespace App\InternalServices\CoBuildAgent;

use App\InternalServices\AbstractService;
use App\InternalServices\DomainException;

class CoBuildAgentInternalService extends AbstractService
{
    public function agentGet($uri, $params, $headers, $needAuth = false){
        if(empty($headers)){
            $headers = [];
        }

        if($needAuth && empty(request()->header('Authorization'))){
            throw new DomainException("not auth" , "401");
        }
        if($needAuth && !empty(request()->header('Authorization'))){
            $headers['Authorization'] = request()->header('Authorization');
        }
        $headers['x-request-id'] = app('requestId');
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

        return $rtRaw;
    }



    public function agentPost($uri, $params, $headers, $needAuth = false){
        if(empty($headers)){
            $headers = [];
        }

        if($needAuth && empty(request()->header('Authorization'))){
            throw new DomainException("not auth" , "401");
        }
        if($needAuth && !empty(request()->header('Authorization'))){
            $headers['Authorization'] = request()->header('Authorization');
        }
        $headers['x-request-id'] = app('requestId');
        $rtRaw = $this->postDataWithHeaders($uri, [
            "headers"=>$headers,
            "json"=>$params
        ]);

        if(!empty($rtRaw['errors'])){
            \Log::error(sprintf(
                "launch chat service post call failed, url: %s  params: %s  headers: %s  result: %s",
                $uri,
                json_encode($params, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                json_encode($headers, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                json_encode($rtRaw, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            ));
            throw new DomainException("open launch chat service call failed", 500);
        }

        \Log::info(sprintf(
            "launch chat service get call result, url: %s  params: %s  headers: %s  result: %s",
            $uri,
            json_encode($params, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            json_encode($headers, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            json_encode($rtRaw, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        ));

        return $rtRaw;
    }
}
