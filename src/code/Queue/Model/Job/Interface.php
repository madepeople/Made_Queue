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

trait Made_Queue_Model_Job_Deferrable
{
    /**
     * Adds itself to the queue in order to have its perform() called at a
     * later stage. If no specific queue identifier is supplied, the default
     * queue will be used.
     *
     * @param string $queue
     * @param int $numRetries  The maximum number of retries for a recoverable error
     */
    public function defer($queue = Made_Queue_Model_Job::DEFAULT_QUEUE,
        $numRetries = 1)
    {
        $job = Mage::getModel('queue/job');
        $job->setHandler($this);
        $job->setNumRetries($numRetries);
        $job->setQueue($queue);
        $job->save()
            ->enqueue();
    }
}
