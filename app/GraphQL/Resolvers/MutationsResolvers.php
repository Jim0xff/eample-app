<?php

namespace App\GraphQL\Resolvers;

use App\Adapters\LoginUser;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Pump\Comment\Service\CommentService;
use Pump\Token\Service\TokenService;
use Pump\User\Services\UserService;

class MutationsResolvers
{
    public function createToken(null $_, array $args, GraphQLContext $context)
    {
        $user = $context->user();
        /** @var LoginUser $user */
        $args['creator'] =  $user->address;
        /** @var $tokenService TokenService */
        $tokenService = resolve('token_service');
        $args['address'] = strtolower($args['address']);
        $args['creatorObj'] = $user;
        $token = $tokenService->createToken($args);
        return $token;
    }

    public function createComment(null $_, array $args, GraphQLContext $context)
    {
        $user = $context->user();
        /** @var LoginUser $user */
        $args['user'] =  $user->address;
        /** @var CommentService $commentService */
        $commentService = resolve("comment_service");
        $comment = $commentService->createComment($args);
        return $comment;
    }

    public function clickLike(null $_, array $args, GraphQLContext $context)
    {
        /** @var LoginUser $user */
        $user = $context->user();
        $args['userId'] = $user->address;
        /** @var CommentService $commentService */
        $commentService = resolve("comment_service");
        $commentService->likeComment($args);
        return true;
    }

    public function followUser(null $_, array $args, GraphQLContext $context)
    {
        /** @var LoginUser $user */
        $user = $context->user();
        $args['user'] = $user->address;
        /** @var $userService UserService */
        $userService =  resolve('user_service');
        $userService->followUser($args);
        return true;
    }

    public function cancelFollowUser(null $_, array $args, GraphQLContext $context)
    {
        /** @var LoginUser $user */
        $user = $context->user();
        $args['user'] = $user->address;
        /** @var $userService UserService */
        $userService =  resolve('user_service');
        $userService->cancelFollow($args);
        return true;
    }

    public function syncTokenTransaction(null $_, array $args, GraphQLContext $context)
    {
        /** @var LoginUser $user */
        $user = $context->user();
        /** @var $tokenService TokenService */
        $tokenService = resolve('token_service');
        $tokenService->syncTokenTransaction($user, $args['currencyAmount'], $args['currencyType'], $args['transactionHash'], $args['transactionType']);
        return true;
    }
}
