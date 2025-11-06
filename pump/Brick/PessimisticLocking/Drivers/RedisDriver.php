<?php

namespace Brick\PessimisticLocking\Drivers;

use Brick\PessimisticLocking\DriverContract;
use Illuminate\Contracts\Redis\Factory;

class RedisDriver implements DriverContract
{

    protected $redis;

    public function __construct(Factory $redis)
    {
        $this->redis = $redis;
    }

    public function lock($lockName, $duration)
    {
        return (bool)$this->redis->command('set', [$lockName, 1, 'EX', $duration, 'NX']);
    }

    public function release($lockName)
    {
        return (bool)$this->redis->command('del', [$lockName]);
    }

}

