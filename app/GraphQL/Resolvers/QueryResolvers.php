<?php

namespace App\GraphQL\Resolvers;

use App\Adapters\LoginUser;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Pump\Comment\Service\CommentService;
use Pump\Token\Service\TokenService;
use Pump\User\Dao\UserDAOModel;
use Pump\User\Services\UserService;

class QueryResolvers
{
    public function userComplexQuery(null $_, array $args, GraphQLContext $context)
    {
        // TODO implement the resolver

        $rt = UserDAOModel::query()->paginate(100);
        $result = [];
        $result['data'] = $rt->items();
        $result['pagination'] = [
            'total' => $rt->total()
        ];
        return $result;
    }

    public function tokenList(null $_, array $args, GraphQLContext $context)
    {
        /** @var $tokenService TokenService */
        $tokenService = resolve('token_service');
        $user = null;
        try{
            $user = $context->user();
        }catch (\Throwable $exception){

        }

        if(!empty($user)){
            $args['userId'] = $user->address;
        }
        $rt =  $tokenService->tokenList($args);

        return ['items' => $rt, 'pagination' => [
            'pageNum'=>$args['pageNum']??1,
            'pageSize'=>$args['pageSize']??10,
        ]];
    }

    public function boughtTokenList(null $_, array $args, GraphQLContext $context)
    {
        /** @var $tokenService TokenService */
        $tokenService = resolve('token_service');
        $rt =  $tokenService->userBoughtTokens($args);
        return $rt;
    }

    public function tokenDetail(null $_, array $args, GraphQLContext $context)
    {
        /** @var $tokenService TokenService */
        $tokenService = resolve('token_service');
        $user = null;
        try{
            $user = $context->user();
        }catch (\Throwable $exception){

        }

        if(!empty($user)){
            $args['userId'] = $user->address;
        }
        $rt =  $tokenService->tokenDetail($args);
        return $rt;
    }

    public function pageSearchComment(null $_, array $args, GraphQLContext $context)
    {
        $user = null;
        try{
            $user = $context->user();
        }catch (\Throwable $exception){

        }

        if(!empty($user)){
            $args['user'] = $user->address;
        }
        $args['page'] = $args['pageNum'];
        $args['perPage'] = $args['pageSize'];
        /** @var CommentService $commentService */
        $commentService = resolve("comment_service");
        $comment = $commentService->getTokenComment($args);
        return $comment;
    }


    public function pageSearchUserComment(null $_, array $args, GraphQLContext $context)
    {
        $user = null;
        try{
            $user = $context->user();
        }catch (\Throwable $exception){

        }

        if(!empty($user)){
            $args['user'] = $user->address;
        }
        $args['page'] = $args['pageNum'];
        $args['perPage'] = $args['pageSize'];
        /** @var CommentService $commentService */
        $commentService = resolve("comment_service");
        $comment = $commentService->userComments($args);
        return $comment;
    }

    public function userLikeList(null $_, array $args, GraphQLContext $context)
    {
        $user = $context->user();

        if(!empty($user)){
            $args['userId'] = $user->address;
        }
        /** @var CommentService $commentService */
        $commentService = resolve("comment_service");
        $args['page'] = $args['pageNum'];
        $args['perPage'] = $args['pageSize'];
        $comment = $commentService->getUserClickLike($args);
        return $comment;
    }


    public function followerList(null $_, array $args, GraphQLContext $context)
    {
        /** @var $userService UserService */
        $userService =  resolve('user_service');
        $args['page'] = $args['pageNum'];
        $args['perPage'] = $args['pageSize'];
        $rt = $userService->followerList($args);

        $pagination = [
            "total" => $rt['pagination']['total'],
            "pageNum" => $args['pageNum']??1,
            "pageSize" => $args['pageSize']??10,
        ];
        $rt["pagination"] = $pagination;
        return $rt;
    }

    public function followingList(null $_, array $args, GraphQLContext $context)
    {
        /** @var $userService UserService */
        $userService =  resolve('user_service');
        $args['page'] = $args['pageNum'];
        $args['perPage'] = $args['pageSize'];
        $rt = $userService->followingList($args);
        $pagination = [
            "total" => $rt['pagination']['total'],
            "pageNum" => $args['pageNum']??1,
            "pageSize" => $args['pageSize']??10,
        ];
        $rt["pagination"] = $pagination;
        return $rt;
    }

    public function tokenHolders(null $_, array $args, GraphQLContext $context)
    {
        /** @var $tokenService TokenService */
        $tokenService = resolve('token_service');
        $rt = $tokenService->tokenHolders($args);
        return $rt;
    }

    public function tradingList(null $_, array $args, GraphQLContext $context)
    {
        /** @var $tokenService TokenService */
        $tokenService = resolve('token_service');
        $args['page'] = $args['pageNum'];
        $args['perPage'] = $args['pageSize'];
        $rt = $tokenService->tradingList($args);
        $pagination = [
            "total" => $rt['pagination']['total'],
            "pageNum" => $args['pageNum']??1,
            "pageSize" => $args['pageSize']??10,
        ];
        $rt["pagination"] = $pagination;
        return $rt;
    }
}
