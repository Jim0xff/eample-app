<?php

namespace Pump\Comment\Dao;

use Illuminate\Database\Eloquent\Model;

class CommentDAOModel extends Model
{
   protected $table = "comment";
   protected $primaryKey = 'id';
   public static function searchComment($params)
   {
       $mdl = self::query();
       if(isset($params['ids'])){
           $mdl->whereIn('id', $params['ids']);
       }
       if(isset($params['token'])) {
           $mdl->where('token', $params['token']);
       }
       if(isset($params['parent_comment_id_null']) && $params['parent_comment_id_null']) {
           $mdl->whereNull('parent_comment_id');
       }
       if(isset($params['parentCommentIds'])) {
           $mdl->whereIn('parent_comment_id', $params['parentCommentIds']);
       }
       if(isset($params['limit'])) {
           $mdl->limit($params['limit']);
       }else{
           $mdl->limit(100);
       }
       return $mdl->orderBy('love_cnt', 'desc')->orderBy('id', 'desc')->get();
   }

   public static function lockCommentById($id)
   {
       return self::query()->lockForUpdate()->find($id);
   }

    public static function searchCommentPaginate($params)
    {
        $mdl = self::query();
        if(isset($params['ids'])){
            $mdl->whereIn('id', $params['ids']);
        }
        if(isset($params['token'])) {
            $mdl->where('token', $params['token']);
        }
        if(isset($params['parent_comment_id_null']) && $params['parent_comment_id_null']) {
            $mdl->whereNull('parent_comment_id');
        }
        if(isset($params['parentCommentIds'])) {
            $mdl->whereIn('parent_comment_id', $params['parentCommentIds']);
        }
        return $mdl->orderBy('love_cnt', 'desc')->orderBy('id', 'desc')->paginate($params["perPage"]);
    }
}
