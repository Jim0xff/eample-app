<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Token extends Model
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
       return $mdl->orderBy('id', 'desc')->get();
    }
}
