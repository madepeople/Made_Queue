<?php

/**
 * The main job model, which contains everything needed to run a queued job
 *
 * @author jonathan@madepeople.se
 */
class Made_Queue_Model_Job extends Mage_Core_Model_Abstract
{

    /**
     * Queues can have names, i actually don't really know why, but they can
     */
    const DEFAULT_QUEUE = 'default';

    /**
     * The job has finally finished completely
     */
    const STATUS_DONE = 'DONE';

    /**
     * The job is pending execution
     */
    const STATUS_PENDING = 'PENDING';

    /**
     * A fatal error occurred, don't run this job again
     */
    const STATUS_FAILED = 'FAILED';

    public function _construct()
    {
        $this->_init('queue/job');
    }

    /**
     * The handler is the object that wakes up in the future to do its thing
     *
     * @param Made_Queue_Model_Job_Interface $job
     */
    public function setHandler(Made_Queue_Model_Job_Interface $job)
    {
        $this->setData('handler', serialize($job));
        return $this;
    }

    /**
     * Put this at the end of the queue
     *
     * @return $this
     */
    public function enqueue()
    {
        $this->getResource()->enqueue($this->getId());
        return $this;
    }

    /**
     * Remove this entry from the queue
     *
     * @return $this
     */
    public function dequeue()
    {
        $this->getResource()->dequeue($this->getId());
        return $this;
    }

}