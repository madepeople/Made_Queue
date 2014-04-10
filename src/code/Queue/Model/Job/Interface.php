<?php

/**
 * Job interface, every job needs to implement this one
 *
 * @author jonathan@madepeople.se
 */
interface Made_Queue_Model_Job_Interface extends Serializable
{
    public function perform();
    public function defer();
}

trait Deferrable
{
    /**
     * Adds itself to the queue in order to have its perform() called at a
     * later stage. If no specific queue identifier is supplied, the default
     * queue will be used.
     *
     * @param string $queue
     */
    public function defer($queue = Made_Queue_Model_Manager::DEFAULT_QUEUE)
    {
        $manager = Mage::getModel('queue/manager');
        $manager->addJob($this, $queue);
    }
}