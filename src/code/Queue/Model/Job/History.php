<?php

/**
 * Contains the outcome of when a job has run
 *
 * @author jonathan@madepeople.se
 */
class Made_Queue_Model_Job_History extends Mage_Core_Model_Abstract
{

    /**
     * The job run completed successfully on queue level (not app level)
     */
    const HISTORY_SUCCESS = 'SUCCESS';

    /**
     * The job resulted in a recoverable error, meaning it should run once again
     */
    const HISTORY_RECOVERABLE_ERROR = 'RECOVERABLE_ERROR';

    /**
     * A fatal error occured, don't run this job again. And possible actually
     * send an alarm somewhere
     */
    const HISTORY_FAILED = 'FATAL_ERROR';

    /**
     * Dat history table
     */
    public function _construct()
    {
        $this->_init('queue/job_history');
    }

}