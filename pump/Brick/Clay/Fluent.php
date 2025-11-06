<?php

namespace Brick\Clay;


use BadMethodCallException;

class Fluent extends \Illuminate\Support\Fluent
{
    public function __call($method, $parameters)
    {
        throw new BadMethodCallException();
    }

}