<?php

namespace Pump\Token\Repository;

use Carbon\Carbon;
use Pump\Token\Dao\TokenDAOModel;
use Pump\Token\DbModel\TokenDbModel;

class TokenRepository
{
    const TOKEN_STATUS_FUNDING  = "FUNDING";
    const TOKEN_STATUS_TRADING = "TRADING";

    public static function queryTokens($params)
    {
        $rawData = TokenDAOModel::queryTokens($params);
        return self::toDbModels($rawData);
    }

    public static function pageQueryTokens($params)
    {
        $pageData = TokenDAOModel::pageQueryTokens($params);
        $items = self::toDbModels($pageData->items());
        return $items;
    }

    public static function createToken(TokenDbModel $tokenDbModel):TokenDbModel
    {
        $daoModel = self::toDaoModel($tokenDbModel);
        $daoModel->created_at = Carbon::now();
        $daoModel->updated_at = Carbon::now();
        $daoModel->status = self::TOKEN_STATUS_FUNDING;
        $daoModel->save();
        return self::toDbModel($daoModel);
    }

    public static function toDaoModel(TokenDbModel $tokenDbModel):TokenDAOModel
    {
        $tokenDaoModel = new TokenDAOModel();
        $tokenDaoModel->id = $tokenDbModel->id;
        $tokenDaoModel->name = $tokenDbModel->name;
        $tokenDaoModel->address = $tokenDbModel->address;
        $tokenDaoModel->desc = $tokenDbModel->desc;
        $tokenDaoModel->content = json_encode($tokenDbModel->content);
        $tokenDaoModel->created_at = $tokenDbModel->createdAt;
        $tokenDaoModel->updated_at = $tokenDbModel->updatedAt;
        $tokenDaoModel->deleted_at = $tokenDbModel->deletedAt;
        $tokenDaoModel->img_url =  $tokenDbModel->imgUrl;
        $tokenDaoModel->status = $tokenDbModel->status;
        $tokenDaoModel->symbol = $tokenDbModel->symbol;
        $tokenDaoModel->creator = $tokenDbModel->creator;
        $tokenDaoModel->co_build_agent_id = $tokenDbModel->coBuildAgentId;
        $tokenDaoModel->sell_at = $tokenDbModel->sellAt;
        $tokenDaoModel->ai_agent_type = $tokenDbModel->aiAgentType;
        $tokenDaoModel->features = $tokenDbModel->features;
        $tokenDaoModel->airdrop_rate = $tokenDbModel->airdropRate;
        $tokenDaoModel->trading_volume = $tokenDbModel->tradingVolume;
        return $tokenDaoModel;
    }

    public static function toDbModel($tokenDaoModel):TokenDbModel
    {

       $tokenDbModel = new TokenDbModel();
       $tokenDbModel->id = $tokenDaoModel->id;
       $tokenDbModel->name = $tokenDaoModel->name;
       $tokenDbModel->address = $tokenDaoModel->address;
       $tokenDbModel->desc = $tokenDaoModel->desc;
       $tokenDbModel->content = json_decode($tokenDaoModel->content, true);
       $tokenDbModel->createdAt = $tokenDaoModel->created_at;
       $tokenDbModel->updatedAt = $tokenDaoModel->updated_at;
       $tokenDbModel->deletedAt = $tokenDaoModel->deleted_at;
       $tokenDbModel->imgUrl = $tokenDaoModel->img_url;
       $tokenDbModel->status = $tokenDaoModel->status;
       $tokenDbModel->symbol = $tokenDaoModel->symbol;
       $tokenDbModel->creator = $tokenDaoModel->creator;
       $tokenDbModel->coBuildAgentId = $tokenDaoModel->co_build_agent_id;
       $tokenDbModel->sellAt = $tokenDaoModel->sell_at;
       $tokenDbModel->aiAgentType = $tokenDaoModel->ai_agent_type;
       $tokenDbModel->features = $tokenDaoModel->features;
       $tokenDbModel->airdropRate = $tokenDaoModel->airdrop_rate;
       $tokenDbModel->tradingVolume = $tokenDaoModel->trading_volume;
       return $tokenDbModel;
    }

    public static function toDbModels($tokenModels){
       $result = [];
       if(!empty($tokenModels)){
           foreach($tokenModels as $tokenModel){
               $result[] = self::toDbModel($tokenModel);
           }
       }
       return $result;
    }
}
