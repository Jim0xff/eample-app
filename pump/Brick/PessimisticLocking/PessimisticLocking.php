<?php

namespace Brick\PessimisticLocking;

use Closure;

class PessimisticLocking implements PessimisticLockingContract
{
    const LOCK_NAME_PREFIX = 'pessimistic_locking';

    /**
     * @var DriverContract
     */
    protected $driver;

    protected $duration;

    public function __construct($driver, $duration = 5)
    {
        $this->driver = $driver;
        $this->duration = $duration;
    }


    public function process($lockName, $attempt, $duration, $waitTime, Closure $closure)
    {

        $realLockName = $this->appendLockName($lockName);

        $attempting = 0;
        while (!$this->getDriver()->lock($realLockName, $waitTime)) {
            $attempting += 1;
            if ($attempting >= $attempt) {
                throw new ConcurrencyException();
            }
            usleep($duration);
        }

        try {
            $rt = $closure();
            return $rt;
        } finally {
            $this->getDriver()->release($realLockName);
        }

    }

    public function getDriver()
    {
        return $this->driver;
    }

    protected function appendLockName($lockName)
    {
        return self::LOCK_NAME_PREFIX . '_' . $lockName;
    }
}

