<?php

namespace Pump\Brick\Lock;

use Brick\Lock\Exceptions\DuplicateException;
use Brick\Lock\Exceptions\ParamsException;
use Carbon\Carbon;
use Illuminate\Support\Facades\Redis;

class RedisLock
{
    const DUPLICATE_CODE = -9999;
    const SUCCESS_CODE = -2000;

    /**
     * @var $lockName
     * 锁名称
     */
    private $lockName;

    /**
     * @var int
     * 锁自动释放时间
     */
    private $expiredSeconds;

    /**
     * @var int
     * 最多尝试获取次数
     */
    private $maxCount;

    /**
     * @var int
     * 当获取失败时 最多尝试次数
     */
    private $sleepMicroSeconds;

    /**
     * @var int
     * 锁值
     */
    private $value = 1;

    public function __construct($lockName, $expiredSeconds = 30, $maxCount = 20, $sleepMicroSeconds = 50000, $value = 1)
    {
        $this->lockName = $lockName;
        $this->expiredSeconds = $expiredSeconds;
        $this->maxCount = $maxCount;
        $this->sleepMicroSeconds = $sleepMicroSeconds;
        $this->value = $value;
    }

    /**
     * 获取互斥锁
     * @return int
     */
    protected function Lock()
    {
        $redis = Redis::connection();
        $int_i = 0;
        while (!$redis->set($this->lockName, $this->value, 'EX', $this->expiredSeconds, 'NX')) {
            if ($int_i >= $this->maxCount) {
                return self::DUPLICATE_CODE;
            }
            $int_i++;
            usleep($this->sleepMicroSeconds);
        }
        return self::SUCCESS_CODE;
    }


    /**
     * 释放锁
     * @param $lock_name
     */
    protected function ReleaseLock()
    {
        $redis = Redis::connection();
        $redis->del($this->lockName);
    }

    /**
     * @param \Closure $closure
     * @throws \Exception
     */
    public function fire(\Closure $closure)
    {
        if (empty($this->lockName)) {
            throw new ParamsException();
        }
        if ($this->Lock() == self::SUCCESS_CODE) {
            try {
                $rt = $closure();
                $this->ReleaseLock();
                return $rt;
            } catch (\Exception $e) {
                $this->ReleaseLock();
                throw $e;
            }
        } else {
            \Log::error('lock_name:' . $this->lockName . 'at:' . Carbon::now()->format("Y-m-d H:i:s"));
            throw new DuplicateException();
        }
    }

}
