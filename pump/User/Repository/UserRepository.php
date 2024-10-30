<?php

namespace Pump\User\Repository;

use Carbon\Carbon;
use Pump\User\DbModel\UserDbModel;
use Pump\User\Dao\UserDAOModel;
use Pump\Utils\StrUtils;

class UserRepository
{
    public static function getUsersByAddressList(array $addressList){
        $rawData = UserDAOModel::getUsersByAddressList($addressList);
        return self::convertToDbModelList($rawData);
    }

    public static function createUser(UserDbModel $userDbModel)
    {
        if(!$userDbModel->getNickName()){
            $userDbModel->setNickName(StrUtils::generateRandomUserName());
        }
        $userDAOModel = self::convertDbModelToDAOModel($userDbModel);
        $userDAOModel->created_at = Carbon::now();
        $userDAOModel->updated_at = Carbon::now();
        $userDAOModel->save();
    }

    public static function convertToDbModel(UserDAOModel $userDAOModel):UserDbModel
    {
        $userDbModel = new UserDbModel();
        $userDbModel->setContent($userDAOModel->content);
        $userDbModel->setAddress($userDAOModel->address);
        $userDbModel->setCreatedAt($userDAOModel->created_at);
        $userDbModel->setUpdatedAt($userDAOModel->updated_at);
        $userDbModel->setDeletedAt($userDAOModel->deleted_at);
        $userDbModel->setNickName($userDAOModel->nick_name);
        $userDbModel->setWalletType($userDAOModel->wallet_type);
        $userDbModel->setHeadImgUrl($userDAOModel->head_img_url);
        return $userDbModel;
    }

    public static function convertToDbModelList($userDAOModels)
    {
        $result = [];
        if(!empty($userDAOModels)){
            foreach ($userDAOModels as $userDAOModel) {
                $result[] = self::convertToDbModel($userDAOModel);
            }
        }
        return $result;
    }

    public static function convertDbModelToDAOModel(UserDbModel $userDbModel)
    {
        $userDAOModel = new UserDAOModel();
        $userDAOModel->content = $userDbModel->getContent();
        $userDAOModel->address = $userDbModel->getAddress();
        $userDAOModel->created_at = $userDbModel->getCreatedAt();
        $userDAOModel->updated_at = $userDbModel->getUpdatedAt();
        $userDAOModel->deleted_at = $userDbModel->getDeletedAt();
        $userDAOModel->nick_name = $userDbModel->getNickName();
        $userDAOModel->wallet_type = $userDbModel->getWalletType();
        $userDAOModel->head_img_url = $userDbModel->getHeadImgUrl();
        return $userDAOModel;
    }

    public static function convertDbModelsToDAOModels(array $userDAOModels)
    {
        $result = [];
        if(!empty($userDAOModels)){
            foreach ($userDAOModels as $userDAOModel) {
                $result[] = self::convertDbModelToDAOModel($userDAOModel);
            }
        }
        return $result;
    }
}
