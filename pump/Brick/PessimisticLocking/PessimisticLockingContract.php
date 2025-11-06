<?php

namespace Brick\PessimisticLocking;

use Closure;

interface PessimisticLockingContract
{
    /**
     * Process logic in locking session.
     *
     * @param $lockName
     * @param Closure $closure
     * @throws ConcurrencyException
     */
    public function process($lockName, $attempt, $duration, $waitTime, Closure $closure);
}
