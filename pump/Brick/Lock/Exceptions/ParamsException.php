<?php namespace Brick\Lock\Exceptions;

use Brick\Clay\DomainException;

class ParamsException extends DomainException
{
    protected $message = '锁名不能为空';
}