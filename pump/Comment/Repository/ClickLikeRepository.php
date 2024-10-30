<?php

namespace Pump\Comment\Repository;

use Carbon\Carbon;
use Pump\Comment\Dao\ClickLikeDAOModel;
use Pump\Comment\DbModel\ClickLikeDbModel;

class ClickLikeRepository
{
    public static function getLikeList($params)
    {
        $clickLikeList = ClickLikeDAOModel::searchLikeClick($params);
        return self::clickLikeDaoModelsToDbModels($clickLikeList);
    }

    public static function searchLikeClickPaginate($params){
        $paginator = ClickLikeDAOModel::searchLikeClickPaginate($params);
        $data = $paginator->items();
        $dataFormat = self::clickLikeDaoModelsToDbModels($data);
        $result = [];
        $pagination = [];
        $pagination['currentPage'] = $paginator->currentPage();
        $pagination['total'] = $paginator->total();
        $pagination['lastPage'] = $paginator->lastPage();
        $result['items'] = $dataFormat;
        $result['pagination'] = $pagination;
        return $result;
    }

    public static function createClickLike(ClickLikeDbModel $clickLikeDbModel):ClickLikeDbModel
    {
        $clickLikeDbModel->createdAt = new Carbon();
        $clickLikeDbModel->updatedAt = new Carbon();
        $clickLikeDAOModel = self::clickLikeDbModelToDaoModel($clickLikeDbModel);
        $clickLikeDAOModel->save();
        return self::clickLikeDaoModelToDbModel($clickLikeDAOModel);
    }

    public static function clickLikeDaoModelToDbModel(ClickLikeDAOModel $clickLikeDAOModel):ClickLikeDbModel
    {
        $clickLikeDbModel = new ClickLikeDbModel();
        $clickLikeDbModel->id = $clickLikeDAOModel->id;
        $clickLikeDbModel->commentId = $clickLikeDAOModel->comment_id;
        $clickLikeDbModel->user = $clickLikeDAOModel->user;
        $clickLikeDbModel->type = $clickLikeDAOModel->type;
        $clickLikeDbModel->status = $clickLikeDAOModel->status;
        $clickLikeDbModel->likedUser = $clickLikeDAOModel->liked_user;
        $clickLikeDbModel->content = json_decode($clickLikeDAOModel->content, true);
        $clickLikeDbModel->createdAt = $clickLikeDAOModel->created_at;
        $clickLikeDbModel->updatedAt = $clickLikeDAOModel->updated_at;
        return $clickLikeDbModel;
    }

    public static function clickLikeDbModelToDaoModel(ClickLikeDbModel $clickLikeDbModel):ClickLikeDAOModel
    {
        $clickLikeDAOModel = new ClickLikeDAOModel();
        $clickLikeDAOModel->id = $clickLikeDbModel->id;
        $clickLikeDAOModel->comment_id = $clickLikeDbModel->commentId ;
        $clickLikeDAOModel->user = $clickLikeDbModel->user;
        $clickLikeDAOModel->type = $clickLikeDbModel->type;
        $clickLikeDAOModel->status = $clickLikeDbModel->status;
        $clickLikeDAOModel->liked_user = $clickLikeDbModel->likedUser;
        $clickLikeDAOModel->content = json_encode($clickLikeDbModel->content);
        $clickLikeDAOModel->created_at = $clickLikeDbModel->createdAt;
        $clickLikeDAOModel->updated_at = $clickLikeDbModel->updatedAt;
        return $clickLikeDAOModel;
    }

    public static function clickLikeDaoModelsToDbModels($clickLikeDAOModels){
        $result = [];
        if(!empty($clickLikeDAOModels)){
            foreach($clickLikeDAOModels as $clickLikeDAOModel){
                $result[] = self::clickLikeDaoModelToDbModel($clickLikeDAOModel);
            }
        }
        return $result;
    }
}
