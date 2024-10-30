<?php

namespace Pump\Comment\DTO;

class CommentDTO
{
    public $id;

    public $token;

    public $user;

    public $replyUser;

    public $parentCommentId;

    public $replyCommentId;

    public $loveCnt;

    public $replyImgUrl;

    public $text;

    public $createdAt;

    public $updatedAt;

    public $subCommentList;

    public $userLiked = false;
}
