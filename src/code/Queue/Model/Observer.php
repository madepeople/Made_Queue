<?php

/**
 * Handles cron job things instead of directly using the worker model
 *
 * @author jonathan@madepeople.se
 */
class Made_Queue_Model_Observer
{

    /**
     * Executes default queue jobs
     */
    public function executeJobs()
    {
        if (!Mage::getStoreConfigFlag('queue/general/enabled')) {
            return;
        }

        Mage::getModel('queue/worker')
            ->executeJobs();
    }

    /**
     * DONE queue garbage collection
     */
    public function gc()
    {
        Mage::getModel('queue/worker')
            ->gc();
    }
}