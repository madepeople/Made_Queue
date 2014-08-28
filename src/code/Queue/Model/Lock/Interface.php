<?php

/**
 * Interface for non-blocking locking classes used to guarantee that that jobs
 * don't overlap
 *
 * @author jonathan@madepeople.se
 */
interface Made_Queue_Model_Lock_Interface
{

    /**
     * Set the lock prefix
     *
     * @param string $prefix
     * @return void
     */
    public function setPrefix($prefix);

    /**
     * Setter for the lock timeout. The lock is valid unless unlocked until
     * this timeout has been reached
     *
     * @param int $timeout
     * @return void
     */
    public function setTimeout($timeout);

    /**
     * Locks the resource by name. Throws Exception if a resource couldn't be
     * accessed for instance. If I/O works and the lock already exists, false
     * is returned
     *
     * @param string $name
     * @return boolean  true if success, false if lock exists
     * @throws Exception
     */
    public function lock($name);

    /**
     * Unlocks a previously locked resource. Return true if success, false if
     * lock doesn't exist
     *
     * @param string $name
     * @return boolean
     * @throw Exception
     */
    public function unlock($name);

    /**
     * Test the existence of a lock
     *
     * @param string $name
     * @return boolean
     */
    public function test($name);
}
