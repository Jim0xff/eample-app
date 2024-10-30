<?php

namespace Pump\Comment\Dao;

use Illuminate\Database\Eloquent\Model;

class ClickLikeDAOModel extends Model
{
    protected $table = 'like_click';

    public static function searchLikeClick($params){
        $mdl = self::query();
        if(isset($params['commentId'])) {
            $mdl->where('comment_id', $params['commentId']);
        }
        if(isset($params['commentIds'])) {
            $mdl->whereIn('comment_id', $params['commentIds']);
        }
        if(isset($params['likedUser']) && $params['likedUser']) {
            $mdl->where('liked_user', $params['likedUser']);
        }
        if(isset($params['statusList'])) {
            $mdl->whereIn('status', $params['statusList']);
        }
        if(isset($params['types'])) {
            $mdl->whereIn('type', $params['types']);
        }
        if(isset($params['user'])) {
            $mdl->where('user', $params['user']);
        }
        return $mdl->orderBy('id', 'desc')->get();
    }

    public static function searchLikeClickPaginate($params){
        $mdl = self::query();
        if(isset($params['commentId'])) {
            $mdl->where('comment_id', $params['commentId']);
        }
        if(isset($params['commentIds'])) {
            $mdl->whereIn('comment_id', $params['commentIds']);
        }
        if(isset($params['likedUser']) && $params['likedUser']) {
            $mdl->where('liked_user', $params['likedUser']);
        }
        if(isset($params['statusList'])) {
            $mdl->whereIn('status', $params['statusList']);
        }
        if(isset($params['types'])) {
            $mdl->whereIn('type', $params['types']);
        }
        if(isset($params['user'])) {
            $mdl->where('user', $params['user']);
        }
        return $mdl->orderBy('id', 'desc')->paginate($params["perPage"]);
    }
}
