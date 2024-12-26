<?php

namespace Pump\Token\Service;

use Pump\Token\Dao\TopOfTheMoonDAOModel;

class TopOfTheMoonService
{
     public static $GENERATE_NUM_MAX = 10;
     public function generateTopOfTheMoon()
     {
        $manualTokens = TopOfTheMoonDAOModel::query()->where('status','active')->where('type','MANUAL')->get()->toArray();
        $needGeneration = self::$GENERATE_NUM_MAX - count($manualTokens);
         /** @var $tokenService TokenService */
         $tokenService = resolve('token_service');
        if ($needGeneration > 0) {
            //先找已发射的

            $tradingTokens = $tokenService->tokenList([
                'statusList'=>['TRADING'],
            ]);
            $needGenerationRecords = [];
            if(!empty($tradingTokens)) {
                usort($tradingTokens, function($pre, $next) {
                    return $pre['nowPrice'] < $next['nowPrice'];
                });
                $needGenerationRecords = array_slice($tradingTokens, 0, $needGeneration);
            }
            if(count($needGenerationRecords) < $needGeneration) {
                //从未发射的token中找
                $fundingTokens = $tokenService->tokenList([
                    'statusList'=>['FUNDING'],
                    'orderBy'=> 'nowPrice',
                ]);
                if(!empty($fundingTokens)) {
                    $needGenerationRecords = array_merge($needGenerationRecords, array_slice($fundingTokens,0,($needGeneration - count($needGenerationRecords))));
                }
            }
            if(!empty($needGenerationRecords)) {
                TopOfTheMoonDAOModel::query()->where('status','active')->where('type','SYSTEM')->update(['status'=>'in_active']);
                foreach ($needGenerationRecords as $needGenerationRecord) {
                    $addTmp = new TopOfTheMoonDAOModel();
//                    $table->string('address')->unique()->comment('代币地址');
//                    $table->string('status')->comment('状态');
//                    $table->longText('content')->nullable()->comment('扩展内容');
//                    $table->string('type')->comment('类型，MANUAL、SYSTEM');
                    $addTmp->address = $needGenerationRecord['id'];
                    $addTmp->status = 'active';
                    $addTmp->content = json_encode(['obj'=>$needGenerationRecord]);
                    $addTmp->type = 'SYSTEM';
                    $addTmp->save();
                }
            }
        }
     }
}
