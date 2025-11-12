<?php

namespace Pump\Token\Dao;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TokenDAOModel extends Model
{
    protected $table = 'token';
    public static function queryTokens($params)
    {
       $mdl = self::query();
       if(isset($params['name'])) {
           $mdl->where('name', $params['name']);
       }
       if(isset($params['statusList'])) {
           $mdl->whereIn('status', $params['statusList']);
       }
       if(isset($params['addressList'])) {
           $mdl->whereIn('address', $params['addressList']);
       }
       if(isset($params['limit'])) {
           $mdl->limit($params['limit']);
       }else{
           $mdl->limit(100);
       }
       if(isset($params['idMin'])) {
           $mdl->where('id','>', $params['idMin']);
       }
       if(isset($params['orderBy'])){
           $mdl->orderBy($params['orderBy'], $params['orderByDirection'] ?? 'desc');
       }else{
           $mdl->orderBy('id', 'desc');
       }
       return $mdl->get();
    }

    public static function pageQueryTokens($params)
    {
        $mdl = self::query();
        if(isset($params['statusList'])) {
            $mdl->whereIn('status', $params['statusList']);
        }
        if(isset($params['addressList'])) {
            $mdl->whereIn('address', $params['addressList']);
        }
        if(!empty($params['name'])) {
            $term = $params['name'];
            $mdl->addSelect(DB::raw("MATCH(name, symbol, `desc`) AGAINST('$term' IN NATURAL LANGUAGE MODE) AS relevance"))
                ->whereFullText(['name', 'symbol', 'desc'], $term)
                ->orderByDesc('relevance');
        }else{
            $mdl->orderBy($params['orderBy'], $params['orderDirection']);
        }

        return  $mdl->simplePaginate($params['pageSize'], ['*'], 'page', $params['page']);
    }
}
