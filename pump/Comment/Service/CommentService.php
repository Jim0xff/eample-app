<?php

namespace Pump\Comment\Service;

use App\InternalServices\DomainException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Pump\Comment\DbModel\ClickLikeDbModel;
use Pump\Comment\DbModel\CommentDbModel;
use Pump\Comment\DTO\ClickLikeDTO;
use Pump\Comment\DTO\CommentDTO;
use Pump\Comment\Repository\ClickLikeRepository;
use Pump\Comment\Repository\CommentRepository;
use Pump\User\Dao\UserDAOModel;
use Pump\User\Repository\UserRepository;
use Pump\User\Services\UserService;

class CommentService
{
    public function createComment($params): CommentDbModel
    {
       return DB::transaction(function () use ($params) {
            $commentDbModel = $this->createParamsToDbModel($params);
            return CommentRepository::createComment($commentDbModel);
        });
    }

    public function likeComment($params)
    {
        return DB::transaction(function () use ($params) {
            $commentId = $params['commentId'];
            $existsLikeClick = ClickLikeRepository::getLikeList(['user'=>$params['userId'], 'commentId'=>$commentId]);
            if(empty($existsLikeClick)){
                $commentDbModel = CommentRepository::lockCommentById($commentId);
                $commentUser = UserDAOModel::getUserByAddressForLock($commentDbModel->user);
                $commentUserContent = json_decode($commentUser->content,true);
                if(empty($commentUserContent['likeCnt'])){
                    $commentUserContent['likeCnt'] = 0;
                }
                $commentUserContent['likeCnt']++;
                $commentUser->content = json_encode($commentUserContent);
                $commentUser->save();
                if($commentDbModel == null){
                    throw new DomainException("comment not fund!");
                }
                $clickLikeModel = $this->createLikeParamsToClickLikeComment($params, $commentDbModel);
                try{
                    ClickLikeRepository::createClickLike($clickLikeModel);
                    $commentDbModel->loveCnt = $commentDbModel->loveCnt+1;
                    CommentRepository::updateComment($commentDbModel);
                }catch (UniqueConstraintViolationException $e){
                    Log::info("duplicate_click_like,params = " . json_encode($params));
                }
            }
        });
    }

    public function getUserClickLike($params)
    {
        if(!empty($params['page'])){
            $params['page'] = 1;
        }
        if(!empty($params['perPage'])){
            $params['perPage'] = 20;
        }
        $clickLikePageData = ClickLikeRepository::searchLikeClickPaginate(
            [
                'likedUser'=>$params['userId'],
                'page'=>$params['page'],
                'perPage'=>$params['perPage']
            ]
        );
        $pageResult = [];
        $pageResult['pagination'] = $clickLikePageData['pagination'];
        $pageResult['items'] = [];
        $userIds = [];
        if(!empty($clickLikePageData['items'])){
            foreach($clickLikePageData['items'] as $item){
                $userIds[] = $item->user;
            }
            $userIds = array_unique($userIds);
            $userInfo = UserRepository::getUsersByAddressList($userIds);
            $userInfoMap = [];
            /** @var $userService UserService */
            $userService = resolve('user_service');
            $userInfoFormat = $userService->userDBModelsToUserDTOs($userInfo);
            foreach($userInfoFormat as $user){
                $userInfoMap[$user->address] = $user;
            }
            $pageResult['items']  = $this->clickDbModelsToDTOs($clickLikePageData['items'], $userInfoMap);
        }
        return $pageResult;
    }

    public function getTokenComment($params)
    {
        $tokenId = $params['tokenId'];
        $searchParam = [];
        $searchParam['token'] = $tokenId;
        $searchParam['parent_comment_id_null'] = true;
        if(empty($params['perPage'])){
            $params['perPage'] = 30;
        }
        if(empty($params['page'])){
            $params['page'] = 1;
        }
        $searchParam['page'] = $params['page'];
        $searchParam['perPage'] = $params['perPage'];
        $commentPageData = CommentRepository::pageSearchComment($searchParam);
        $pageResult = [];
        $pageResult['pagination'] = $commentPageData['pagination'];
        $pageResult['items'] = [];
        $totalCommentIds = [];
        if(!empty($commentPageData['items'])) {
            $subSearchParam = [];
            $parentIds = [];
            $userIds = [];
            foreach ($commentPageData['items'] as $comment) {
                $parentIds[] = $comment->id;
                $userIds[] = $comment->user;
                $totalCommentIds[] = $comment->id;
            }
            $subSearchParam['parentCommentIds'] = $parentIds;
            $subSearchParam['limit'] = 5;
            $subCommentList = CommentRepository::searchComment($subSearchParam);
            $subCommentMap = [];
            if(!empty($subCommentList)) {
                foreach ($subCommentList as $subComment) {
                    $userIds[] = $subComment->user;
                    $totalCommentIds[] = $subComment->id;
                    if($subComment->replyUser) {
                        $userIds[] = $subComment->replyUser;
                    }
                    $subCommentMapSingle = [];
                    if(!empty($subCommentMap[$subComment->parentCommentId])){
                        $subCommentMapSingle = $subCommentMap[$subComment->parentCommentId];
                    }
                    $subCommentMapSingle[] = $subComment;
                    $subCommentMap[$subComment->parentCommentId] = $subCommentMapSingle;
                }
            }

            $userIds = array_unique($userIds);
            $userInfo = UserRepository::getUsersByAddressList($userIds);

            $userInfoMap = [];
            if(!empty($userInfo)) {
                foreach ($userInfo as $userInfoItem) {
                    $userInfoMap[$userInfoItem->getAddress()] = $userInfoItem;
                }
            }
            $clickLikeMap = [];
            if(!empty($params['userId']) && !empty($totalCommentIds)) {
                $clickLikeList = ClickLikeRepository::getLikeList(['user'=>$params['userId'], 'commentIds'=>$totalCommentIds]);
                if(!empty($clickLikeList)){
                    foreach ($clickLikeList as $clickLike) {
                        $clickLikeMap[$clickLike->commentId] = $clickLike;
                    }
                }
            }
            $commentDTOList = $this->commentDbModelsToDbModels($commentPageData['items'], $userInfoMap, $clickLikeMap);
            foreach ($commentDTOList as $commentDTO) {
                if(!empty($subCommentMap[$commentDTO->id])){
                    $commentDTO->subCommentList = $this->commentDbModelsToDbModels($subCommentMap[$commentDTO->id], $userInfoMap, $clickLikeMap);
                }
            }
            $pageResult['items'] = $commentDTOList;
        }
        return $pageResult;
    }

    public function createParamsToDbModel($params):CommentDbModel
    {
        $dbModel = new CommentDbModel();
        $dbModel->token = $params['token'];
        $contentObj = [];
        $contentObj['text'] = $params['text'];
        $contentObj['imgUrl'] = $params['imgUrl'];
        $dbModel->content = $contentObj;
        $dbModel->parentCommentId = $params['parentCommentId']??null;
        $dbModel->replyCommentId = $params['replyCommentId']??null;
        $replyId = null;
        if(!empty($params['parentCommentId'])){
            $replyId = $params['parentCommentId'];
            if(!empty($params['replyCommentId'])){
                $replyId = $params['replyCommentId'];
            }
        }
        if($replyId){
            $replyModel = CommentRepository::searchComment(['ids'=>[$replyId]]);
            if(!empty($replyModel)){
                $replyModelSingle = $replyModel[0];
                $dbModel->replyUser = $replyModelSingle->user;
            }
        }
        $dbModel->user = $params['user'];
        return $dbModel;
    }

    public function commentDbModelToDTO(CommentDbModel $commentDbModel, $userDbModelMap, $clickLikeMap):CommentDTO{
        $commentDto = new CommentDTO();
        $commentDto->id = $commentDbModel->id;
        $commentDto->token = $commentDbModel->token;
        if(!empty($userDbModelMap[$commentDbModel->user])) {
            $commentDto->user = $userDbModelMap[$commentDbModel->user];
        }
        if($commentDbModel->replyUser && $userDbModelMap[$commentDbModel->replyUser]){
            $commentDto->replyUser = $userDbModelMap[$commentDbModel->replyUser];
        }
        $commentDto->parentCommentId = $commentDbModel->parentCommentId;
        $commentDto->replyCommentId = $commentDbModel->replyCommentId;
        $commentDto->loveCnt = $commentDbModel->loveCnt;
        $commentDto->replyImgUrl = $commentDbModel->content['imgUrl'];
        $commentDto->text = $commentDbModel->content['text'];
        $commentDto->createdAt = $commentDbModel->createdAt;
        $commentDto->updatedAt = $commentDbModel->updatedAt;
        if(!empty($clickLikeMap[$commentDbModel->id])){
            $commentDto->userLiked = true;
        }
        return $commentDto;
    }

    public function commentDbModelsToDbModels($commentDbModels, $userDbModelMap, $clickLikeMap){
        $result = [];
        if(!empty($commentDbModels)){
            foreach ($commentDbModels as $commentDbModel) {
                $result[] = $this->commentDbModelToDTO($commentDbModel, $userDbModelMap, $clickLikeMap);
            }
        }
        return $result;
    }

    public function clickDbModelsToDTOs($clickLikeDbModels, $userDTOMap)
    {
        $result = [];
        if(!empty($clickLikeDbModels)){
            foreach ($clickLikeDbModels as $clickLikeDbModel) {
                $result[] = $this->clickDbModelToDTO($clickLikeDbModel, $userDTOMap);
            }
        }
        return $result;
    }

    public function clickDbModelToDTO(ClickLikeDbModel $clickLikeDbModel, $userDTOMap):ClickLikeDTO
    {
        $clickLikeDTO = new ClickLikeDTO();
        $clickLikeDTO->id = $clickLikeDbModel->id;
        if(!empty($userDTOMap[$clickLikeDbModel->user])) {
            $clickLikeDTO->clickUser = $userDTOMap[$clickLikeDbModel->user];
        }
        $commentTmp = [];
        $commentTmp['text'] = $clickLikeDbModel->content['commentText'];
        $commentTmp['imgUrl'] = $clickLikeDbModel->content['commentImgUrl'];
        $clickLikeDTO->comment = $commentTmp;

        return $clickLikeDTO;
    }

    public function createLikeParamsToClickLikeComment($params, CommentDbModel $commentDbModel):ClickLikeDbModel
    {
        $clickLikeDbModel = new ClickLikeDbModel();
        $clickLikeDbModel->commentId = $params['commentId'];
        $clickLikeDbModel->user = $params['userId'];
        $clickLikeDbModel->type = "LIKE";
        $clickLikeDbModel->status = "ACTIVE";
        $clickLikeDbModel->likedUser = $params['likedUser'];
        $contentObj = [];
        $contentObj['commentText'] = $commentDbModel->content['text'];
        $contentObj['commentImgUrl'] = $commentDbModel->content['imgUrl'];
        $clickLikeDbModel->content = $contentObj;
        return $clickLikeDbModel;
    }
}
