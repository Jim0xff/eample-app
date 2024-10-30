<?php

namespace Pump\Comment\Repository;

use Carbon\Carbon;
use Pump\Comment\Dao\CommentDAOModel;
use Pump\Comment\DbModel\CommentDbModel;

class CommentRepository
{
    public static function searchComment($params)
    {
       return self::commentDaoModelsToDbModels(CommentDAOModel::searchComment($params));
    }

    public static function lockCommentById($id)
    {
        $rawData = CommentDAOModel::lockCommentById($id);
        if($rawData){
            return self::commentDaoModelToDbModel(CommentDAOModel::lockCommentById($id));
        }
        return null;
    }

    public static function pageSearchComment($params)
    {
        $paginator = CommentDAOModel::searchCommentPaginate($params);
        $data = $paginator->items();
        $dataFormat = self::commentDaoModelsToDbModels($data);
        $result = [];
        $pagination = [];
        $pagination['currentPage'] = $paginator->currentPage();
        $pagination['total'] = $paginator->total();
        $pagination['lastPage'] = $paginator->lastPage();
        $result['items'] = $dataFormat;
        $result['pagination'] = $pagination;
        return $result;
    }

    public static function createComment(CommentDbModel $commentDbModel): CommentDbModel
    {
        $commentDaoModel = self::commentDbModelToDaoModel($commentDbModel);
        $commentDaoModel->created_at = Carbon::now();
        $commentDaoModel->updated_at = Carbon::now();
        $commentDaoModel->love_cnt = 0;
        $commentDaoModel->save();
        return self::commentDaoModelToDbModel($commentDaoModel);
    }

    public static function updateComment(CommentDbModel $commentDbModel)
    {
        $commentDaoModel = self::commentDbModelToDaoModel($commentDbModel);
        $commentDaoModel->exists = true;
        $commentDaoModel->updated_at = Carbon::now();
        $commentDaoModel->save();
    }

    public static function commentDaoModelToDbModel(CommentDAOModel $commentDaoModel):CommentDbModel
    {
        $dbModel = new CommentDbModel();
        $dbModel->id = $commentDaoModel->id;
        $dbModel->token = $commentDaoModel->token;
        $dbModel->content = json_decode($commentDaoModel->content, true);
        $dbModel->createdAt = $commentDaoModel->created_at;
        $dbModel->updatedAt = $commentDaoModel->updated_at;
        $dbModel->loveCnt = $commentDaoModel->love_cnt;
        $dbModel->deletedAt = $commentDaoModel->deleted_at;
        $dbModel->parentCommentId = $commentDaoModel->parent_comment_id;
        $dbModel->replyCommentId = $commentDaoModel->reply_comment_id;
        $dbModel->replyUser = $commentDaoModel->reply_user;
        $dbModel->user = $commentDaoModel->user;
        return $dbModel;
    }

    public static function commentDbModelToDaoModel(CommentDbModel $commentDbModel):CommentDAOModel
    {
        $daoModel = new CommentDAOModel();
        $daoModel->id = $commentDbModel->id;
        $daoModel->token = $commentDbModel->token;

        $daoModel->content = json_encode($commentDbModel->content);
        $daoModel->created_at = $commentDbModel->createdAt;
        $daoModel->updated_at = $commentDbModel->updatedAt;
        $daoModel->love_cnt = $commentDbModel->loveCnt;
        $daoModel->deleted_at = $commentDbModel->deletedAt;
        $daoModel->parent_comment_id = $commentDbModel->parentCommentId;
        $daoModel->reply_comment_id = $commentDbModel->replyCommentId;
        $daoModel->reply_user = $commentDbModel->replyUser;
        $daoModel->user = $commentDbModel->user;
        return $daoModel;
    }

    public static function commentDaoModelsToDbModels($commentDaoModels)
    {
        $result = array();
        if (!empty($commentDaoModels)) {
            foreach ($commentDaoModels as $commentDaoModel) {
                $result[] = self::commentDaoModelToDbModel($commentDaoModel);
            }
        }
        return $result;
    }
}
