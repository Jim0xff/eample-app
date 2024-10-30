<?php

namespace Pump\User\Dao;

use Illuminate\Database\Eloquent\Model;

class UserFollowDAOModel extends Model
{
    protected $table = 'user_follow_record';

    public static function getFollowersPagination($params)
    {
        $mdl = self::query();
        if(isset($params['followed'])) {
            $mdl->where('followed', $params['followed']);
        }
        if(isset($params['follower'])) {
            $mdl->where('follower', $params['follower']);
        }
        if(isset($params['statusList'])) {
            $mdl->whereIn('status', $params['statusList']);
        }
        if(isset($params['limit'])) {
            $mdl->limit($params['limit']);
        }else{
            $mdl->limit(100);
        }
        return $mdl->orderBy('id', 'desc')->paginate($params["perPage"]);
    }

    public static function getFollowers($params)
    {
        $mdl = self::query();
        if(isset($params['followed'])) {
            $mdl->where('followed', $params['followed']);
        }
        if(isset($params['follower'])) {
            $mdl->where('follower', $params['follower']);
        }
        if(isset($params['statusList'])) {
            $mdl->whereIn('status', $params['statusList']);
        }
        if(isset($params['limit'])) {
            $mdl->limit($params['limit']);
        }else{
            $mdl->limit(100);
        }
        return $mdl->orderBy('id', 'desc')->get();
    }
}
