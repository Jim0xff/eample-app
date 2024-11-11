<?php

namespace App\Http\Controllers;

use App\Adapters\LoginUser;
use Illuminate\Http\Request;
use Pump\Comment\Service\CommentService;

class CommentController extends Controller
{
    public function createComment(Request $request)
    {
        $params = $request->all();
        /** @var LoginUser $user */
        $user = auth()->user();
        $params['user'] = $user->address;
        /** @var CommentService $commentService */
        $commentService = resolve("comment_service");
        $comment = $commentService->createComment($params);
        return response()->json(['data' => $comment, 'code' => 200]);
    }

    public function pageSearchComment(Request $request)
    {
        $params = $request->all();
        /** @var LoginUser $user */
        $user = auth()->user();
        if(!empty($user)){
            $params['user'] = $user->address;
        }
        /** @var CommentService $commentService */
        $commentService = resolve("comment_service");
        $data = $commentService->getTokenComment($params);
        return response()->json(['data' => $data, 'code' => 200]);
    }

    public function userComments(Request $request)
    {
        $params = $request->all();
        /** @var LoginUser $user */
        $user = auth()->user();
        /** @var CommentService $commentService */
        $commentService = resolve("comment_service");
        $data = $commentService->userComments($params);
        return response()->json(['data' => $data, 'code' => 200]);
    }

    public function clickLike(Request $request)
    {
        $params = $request->all();
        /** @var LoginUser $user */
        $user = auth()->user();
        $params['userId'] = $user->address;
        /** @var CommentService $commentService */
        $commentService = resolve("comment_service");
        $commentService->likeComment($params);
        return response()->json(['data' => true, 'code' => 200]);
    }

    public function clickLikeList(Request $request)
    {
        $params = $request->all();
        /** @var CommentService $commentService */
        $commentService = resolve("comment_service");
        /** @var LoginUser $user */
        $user = auth()->user();
        $params['userId'] = $user->address;
        return response()->json(['data' => $commentService->getUserClickLike($params), 'code' => 200]);
    }
}
