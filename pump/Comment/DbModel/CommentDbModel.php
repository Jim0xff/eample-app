<?php

namespace Pump\Comment\DbModel;

class CommentDbModel
{
  public $id;

  public $token;

  public $user;

  public $replyUser;

  public $parentCommentId;

  public $replyCommentId;

  public $loveCnt;

  public $content;

  public $createdAt;

  public $updatedAt;

  public $deletedAt;
}
