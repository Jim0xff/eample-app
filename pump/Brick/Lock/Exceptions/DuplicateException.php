<?php namespace Brick\Lock\Exceptions;

use Brick\Clay\DomainException;

class DuplicateException extends DomainException
{
    protected $message = '服务器繁忙,请稍后再试';
    protected $code = 423;
}