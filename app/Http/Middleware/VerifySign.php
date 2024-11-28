<?php

namespace App\Http\Middleware;

use Aijihui\OpenPlatform\Models\Collaborator;
use App\InternalServices\DomainException;
use Closure;

class VerifySign
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(!env('APP_DEBUG')){
            $content = $request->getContent();
            $queryString = $request->getQueryString();
            parse_str($queryString, $query);

            $secretKey = $query['sign']??false;

            if (!$secretKey) {
                throw new DomainException('The request is missing sign parameter.', 404);
            };

            $appKey = env('APP_KEY','');

            if (isset($query['appId'])) {
                $appKey = $this->handleAppId($query['appId']);
            }

            $contentArray = array('body' => $content);
            $signArray = array_merge($contentArray, $query);

            $result = self::signRule($signArray, $appKey);

            if ($result != $secretKey) {
                throw new DomainException('The secretKey is wrong ,Check the parameter.', 401);
            }
        }

        return $next($request);
    }

    /*
     * 签名规则
     */

    public static function signRule($parameter, $appKey)
    {
        $signArray = array_filter($parameter);

        unset($signArray['sign']);
        //签名步骤一：按字典序排序
        ksort($signArray);
        $string = http_build_query($signArray);
        //签名步骤二：在string后加入KEY
        $string = $string . $appKey;
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);

        return $result;
    }

    /*
     * 分离出APPID
     */

    protected function handleAppId($appId)
    {
        if (!$appId) {
            throw new DomainException('The request is missing appId parameter.', 404);
        }

        $appInfo = Collaborator::query()->where('app_id', $appId)->first();

        if ($appInfo) {
            $appKey = $appInfo->app_key;
        } else {
            throw new DomainException('The appId is wrong.', 401);
        }
        return $appKey;
    }
}

