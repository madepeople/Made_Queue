<?php

class Made_Queue_Model_Manager extends Mage_Core_Model_Abstract
{
    const MAX_JOB_RETRIES = 2;
    const DEFAULT_QUEUE = 'default';

    public function _construct()
    {
        $this->_init('queue/manager');
    }

    public function addJob(Made_Queue_Model_Job_Interface $job)
    {
        $clone = clone $this;
        $clone->clearInstance();
        $clone->setHandler(serialize($job))
            ->setCreatedAt(new Zend_Db_Expr('NOW()'))
            ->save();
    }

    public function getPendingJobs()
    {
        $jobs = array();
        foreach ($this->getResource()->getPendingJobs() as $job) {
            $jobs[] = array(
                'id' => $job['job_id'],
                'handler' => unserialize($job['handler'])
            );
        }
        return $jobs;
    }

    public function recordJobFinishedAt($job, $datetime)
    {
        $this->getResource()->recordJobFinishedAt($job['id'], $datetime);
    }

    public function recordJobFailedAt($job, $e, $datetime)
    {
        $this->getResource()->recordJobFailedAt($job['id'], $e, $datetime);
    }

    public function incrementFailedAttempts($job)
    {
        // @TODO: Implement me to allow job retries
        $this->getResource()->incrementFailedAttempts($job['id']);
        $failedAttempts = $this->getResource()->getFailedAttempts($job['id']);
        if ($failedAttempts < self::MAX_JOB_RETRIES) {
            return true;
        }
        return false;
    }
}