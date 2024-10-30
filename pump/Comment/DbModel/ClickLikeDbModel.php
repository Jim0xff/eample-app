<?php

namespace Pump\Comment\DbModel;

class ClickLikeDbModel
{
    public $id;

    public $commentId;

    public $user;

    public $type;

    public $status;

    public $likedUser;

    public $content;

    public $createdAt;

    public $updatedAt;

    public $deletedAt;
}
