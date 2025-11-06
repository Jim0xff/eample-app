<?php
namespace Brick\PessimisticLocking;

interface DriverContract
{
    /**
     *  Get the lock.
     *
     * @param string $lockName
     * @param int $duration
     * @return bool
     */
    public function lock($lockName, $duration);

    /**
     * Release the lock.
     *
     * @param string $lockName
     * @return bool
     */
    public function release($lockName);
}

