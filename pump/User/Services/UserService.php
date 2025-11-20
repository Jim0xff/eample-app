<?php

namespace Pump\User\Services;

use App\InternalServices\DomainException;
use App\InternalServices\LazpadTaskService\LazpadTaskService;
use Carbon\Carbon;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Pump\User\Dao\UserDAOModel;
use Pump\User\Dao\UserFollowDAOModel;
use Pump\User\DbModel\UserDbModel;
use Pump\User\Dto\FollowUserDTO;
use Pump\User\Dto\UserDTO;
use Pump\User\Repository\UserRepository;
use Pump\User\Request\CreateUserRequest;
use Pump\Utils\TimeSwitcher;

class UserService
{
    public function createUser(CreateUserRequest $request)
    {

        $exists = UserRepository::getUsersByAddressList([$request->address]);
        if(empty($exists)){
            try{
                UserRepository::createUser($this->userCreateRequestToDbModel($request));
            }catch (UniqueConstraintViolationException $e){
                Log::info("duplicate_create_user,params = " . json_encode($request));
            }
        }
        return $this->login(['address'=>$request->address]);
    }

    public function createUserNotLogin(CreateUserRequest $request)
    {

        $exists = UserRepository::getUsersByAddressList([$request->address]);
        if(empty($exists)){
            try{
                UserRepository::createUser($this->userCreateRequestToDbModel($request));
            }catch (UniqueConstraintViolationException $e){
                Log::info("duplicate_create_user,params = " . json_encode($request));
            }
        }

        return null;
    }

    public function editUser($params)
    {
        DB::transaction(function () use ($params) {
            $user = UserDAOModel::getUserByAddressForLock($params['user']);
            $needUpdate = false;
            if(!empty($params['nickName'])){
                $user->nick_name = $params['nickName'];
                $needUpdate = true;
            }
            if(!empty($params['headImgUrl'])){
                $user->head_img_url = $params['headImgUrl'];
                $needUpdate = true;
            }
            if(!empty($params['bio'])){
                $userContent = json_decode($user->content, true);
                $userContent['bio'] = $params['bio'];
                $user->content = json_encode($userContent);
                $needUpdate = true;
            }

            if($needUpdate){
                $user->save();
                if(!empty($params['user'])){
                    /** @var LazpadTaskService $taskService */
                    $taskService = resolve('lazpad_task_service');
                    $taskService->postDataWithHeaders('user/updateUserById', [
                        'headers'=>[
                            'Authorization' => request()->header('Authorization'),
                            "traceId"=> app('requestId'),
                        ],
                        'json'=> [
                            'id' => $params['userInfo']->id,
                            'name' => $params['nickName'],
                            'content' =>[
                                'headImgUrl' =>  $params['headImgUrl'],
                            ]
                        ]
                    ]);
                }

            }
        });
    }

    public function getSingleUserDTO($userAddress, $loginUserAddress)
    {
        $userList = UserRepository::getUsersByAddressList([$userAddress]);
        if(empty($userList)){
            return null;
        }
        $userDTO = $this->userDBModelToUserDTO($userList[0]);
        if($loginUserAddress){
            $followersPageData = UserFollowDAOModel::getFollowers([
                "follower" => $loginUserAddress,
                "followed" => $userAddress,
                "statusList" => ["ACTIVE"],
            ]);
            if(!empty($followersPageData->toArray())){
                $userDTO->followed = true;
            }
        }

        return $userDTO;
    }

    public function followUser($params)
    {
        if($params['targetUser'] == $params['user']){
            throw new DomainException("can not follow your self!");
        }
        DB::transaction(function () use ($params) {
            $followInfo = UserFollowDAOModel::getFollowers([
                "followed" => $params['targetUser'],
                "follower" => $params['user']
            ]);
            $targetUser = UserDAOModel::getUserByAddressForLock($params['targetUser']);
            $users = UserDAOModel::getUsersByAddressList(['address'=>[$params['user']]]);
            $currentUser = $users[0];
            $targetUserContent = json_decode($targetUser->content, true);
            if(empty($targetUserContent['followedCnt'])){
                $targetUserContent['followedCnt'] = 0;
            }
            $targetUserContent['followedCnt']++;
            $targetUser->content = json_encode($targetUserContent);
            $targetUser->save();

            $currentUserContent = json_decode($currentUser->content, true);
            if(empty($currentUserContent['followingCnt'])){
                $currentUserContent['followingCnt'] = 0;
            }
            $currentUserContent['followingCnt']++;
            $currentUser->content = json_encode($currentUserContent);
            $currentUser->save();

            if(!empty($followInfo->toArray()) && $followInfo[0]->status == "ACTIVE"){
                throw new DomainException("has followed!");
            }
            $followModel = null;
            if(!empty($followInfo->toArray())){
                $followModel = $followInfo[0];
                $followModel->status = "ACTIVE";
                $followModel->updated_at = Carbon::now();
                $followModel->follow_at = Carbon::now();
            }else{
                $followModel = new UserFollowDAOModel();
                $followModel->follower = $params['user'];
                $followModel->followed = $params['targetUser'];
                $followModel->status = "ACTIVE";
                $followModel->follow_at = Carbon::now();
                $followModel->created_at = Carbon::now();
                $followModel->updated_at = Carbon::now();
            }
            $followModel->save();
        });
    }

    public function cancelFollow($params)
    {
        DB::transaction(function () use ($params) {
            $followInfo = UserFollowDAOModel::getFollowers([
                "followed" => $params['targetUser'],
                "follower" => $params['user'],
                "status" => "ACTIVE"
            ]);
            if(empty($followInfo->toArray())){
                throw new DomainException("not followed!");
            }

            $targetUser = UserDAOModel::getUserByAddressForLock($params['targetUser']);
            $users = UserDAOModel::getUsersByAddressList(['address'=>[$params['user']]]);
            $currentUser = $users[0];
            $targetUserContent = json_decode($targetUser->content, true);
            $targetUserContent['followedCnt']--;
            $targetUser->content = json_encode($targetUserContent);
            $targetUser->save();

            $currentUserContent = json_decode($currentUser->content, true);
            $currentUserContent['followingCnt']--;
            $currentUser->content = json_encode($currentUserContent);
            $currentUser->save();

            $followModel = $followInfo[0];
            $followModel->status = "IN_ACTIVE";
            $followModel->updated_at = Carbon::now();
            $followModel->cancel_follow_at = Carbon::now();
            $followModel->save();
        });
    }

    public function followerList($params)
    {
        $followersPageData = UserFollowDAOModel::getFollowersPagination([
           "followed" => $params['user'],
           "statusList" => ["ACTIVE"],
            "page" => $params['page']??1,
            "perPage" => $params['perPage']??10,
        ]);
        $pageResult = [];
        $pagination = [];
        $pagination['currentPage'] = $followersPageData->currentPage();
        $pagination['total'] = $followersPageData->total();
        $pagination['lastPage'] = $followersPageData->lastPage();
        $pageResult['pagination'] = $pagination;

        $userIds = [];
        $items = [];
        if(!empty($followersPageData->items())){
            foreach($followersPageData->items() as $item){
                $userIds[] = $item->follower;
            }
            $userIds = array_unique($userIds);
            $userRaw = UserRepository::getUsersByAddressList($userIds);
            $userMap = [];
            $userFormatList = $this->userDBModelsToUserDTOs($userRaw);
            foreach($userFormatList as $user){
                $userMap[$user->address] = $user;
            }
            foreach($followersPageData->items() as $item){
                if(!empty($userMap[$item->follower])){
                    $userDTO = $userMap[$item->follower];
                    $followUserDTO = new FollowUserDTO();
                    $followUserDTO->followAt = $item->follow_at;
                    $followUserDTO->address = $item->follower;
                    $followUserDTO->nickName = $userDTO->nickName;
                    $followUserDTO->headImgUrl = $userDTO->headImgUrl;
                    $items[] = $followUserDTO;
                }
            }
        }
        $pageResult['items'] = $items;
        return $pageResult;
    }

    public function followingList($params)
    {
        $followersPageData = UserFollowDAOModel::getFollowersPagination([
            "follower" => $params['user'],
            "statusList" => ["ACTIVE"],
            "page" => $params['page']??1,
            "perPage" => $params['perPage']??10,
        ]);
        $pageResult = [];
        $pagination = [];
        $pagination['currentPage'] = $followersPageData->currentPage();
        $pagination['total'] = $followersPageData->total();
        $pagination['lastPage'] = $followersPageData->lastPage();
        $pageResult['pagination'] = $pagination;

        $userIds = [];
        $items = [];
        if(!empty($followersPageData->items())){
            foreach($followersPageData->items() as $item){
                $userIds[] = $item->follower;
            }
            $userIds = array_unique($userIds);
            $userRaw = UserRepository::getUsersByAddressList($userIds);
            $userMap = [];
            $userFormatList = $this->userDBModelsToUserDTOs($userRaw);
            foreach($userFormatList as $user){
                $userMap[$user->address] = $user;
            }
            foreach($followersPageData->items() as $item){
                if(!empty($userMap[$item->follower])){
                    $userDTO = $userMap[$item->follower];
                    $followUserDTO = new FollowUserDTO();
                    $followUserDTO->followAt = $item->follow_at;
                    $followUserDTO->address = $item->follower;
                    $followUserDTO->nickName = $userDTO->nickName;
                    $followUserDTO->headImgUrl = $userDTO->headImgUrl;
                    $items[] = $followUserDTO;
                }
            }
        }
        $pageResult['items'] = $items;
        return $pageResult;
    }

    public function userCreateRequestToDbModel(CreateUserRequest $request)
    {
        $userDbModel = new UserDbModel();
        $userDbModel->setNickName($request->nickName);

        $userDbModel->setAddress($request->address);
        $userDbModel->setHeadImgUrl($request->headImgUrl);
        $contentJSON = [];
        $contentJSON['website'] = $request->website;
        $contentJSON['telegramLink'] = $request->telegramLink;
        $contentJSON['twitterLink'] = $request->twitterLink;
        $contentJSON['followedCnt'] = 0;
        $contentJSON['followingCnt'] = 0;
        $userDbModel->setContent(json_encode($contentJSON));

        if(!$request->walletType){
            $request->walletType = ("MetaMask");
        }
        $userDbModel->setWalletType($request->walletType);

        return $userDbModel;
    }

    public function userDBModelToUserDTO(UserDbModel $userDbModel):UserDTO{
        $userDTO = new UserDTO();
        $userDTO->setNickName($userDbModel->getNickName());
        $userDTO->setAddress($userDbModel->getAddress());
        $userDTO->setHeadImgUrl($userDbModel->getHeadImgUrl());
        $userDTO->setWalletType($userDbModel->getWalletType());
        $contentObj = json_decode($userDbModel->getContent(), true);
        $userDTO->followedCnt = $contentObj['followedCnt']??0;
        $userDTO->followingCnt = $contentObj['followingCnt']??0;
        $userDTO->likeCnt = $contentObj['likeCnt']??0;
        $userDTO->setContent($contentObj);
        return $userDTO;
    }

    public function userDBModelsToUserDTOs($userDbModels)
    {
        $result = [];
        if(!empty($userDbModels)){
            foreach($userDbModels as $userDbModel){
                $result[] = $this->userDBModelToUserDTO($userDbModel);
            }
        }
        return $result;
    }

    public function login($params)
    {
        $userAddress = $params['address'];
        $userInfo = $this->getSingleUserDTO($userAddress, null);
        if($userInfo == null){
            throw new DomainException("user not exists");
        }

        $userInfoArr = json_decode(json_encode($userInfo), true);
        $uniqueKey = TimeSwitcher::getMicroSeconds() . rand(1000, 9999);
        $storeUserInToken = array_merge($userInfoArr,['tokenUniqueKey' => $uniqueKey]);

        /** @var $loginTokenService LoginToken */
        $loginTokenService = resolve('login_token_service');
        return $loginTokenService->saveToken($userAddress, json_encode($storeUserInToken));
    }
}
