<?php
/**
 * Queue Management Job Worker
 *
 * @author jonathan@madepeople.se
 */
class Made_Queue_Model_Worker
{

    /**
     * Log that the job has succeeded
     *
     * @param Made_Queue_Model_Job_Interface $job
     */
    public function logJobSuccess(Made_Queue_Model_Job $job)
    {
        Mage::getModel('queue/job_history')
            ->setJobId($job->getId())
            ->setStatus(Made_Queue_Model_Job_History::HISTORY_SUCCESS)
            ->save();

        $job->setStatus(Made_Queue_Model_Job::STATUS_DONE)
            ->save();
    }

    /**
     * Log a recoverable error
     *
     * @param Made_Queue_Model_Job_Interface $job
     * @param Exception $e
     */
    public function logJobRecoverableError(Made_Queue_Model_Job $job, Exception $e)
    {
        $message = 'Caught ' . get_class($e) . ": \n\n" . $e->getTraceAsString();

        Mage::getModel('queue/job_history')
            ->setJobId($job->getId())
            ->setStatus(Made_Queue_Model_Job_History::HISTORY_RECOVERABLE_ERROR)
            ->setMessage($message)
            ->save();

        $job->setStatus(Made_Queue_Model_Job::STATUS_PENDING)
            ->save();
    }

    /**
     * Log job failure
     *
     * @param Made_Queue_Model_Job_Interface $job
     * @param Exception $e
     */
    public function logJobFailed(Made_Queue_Model_Job $job, Exception $e)
    {
        $message = 'Caught ' . get_class($e) . " with message '" . $e->getMessage() . "': \n\n" . $e->getTraceAsString();

        Mage::getModel('queue/job_history')
            ->setJobId($job->getId())
            ->setStatus(Made_Queue_Model_Job_History::HISTORY_FAILED)
            ->setMessage($message)
            ->save();

        $job->setStatus(Made_Queue_Model_Job::STATUS_FAILED)
            ->save();
    }

    /**
     * Fetch jobs from the queue and execute them. Only runs if the queue is
     * enabled in admin, which I'm not really sure is a good idea actually
     */
    public function executeJobs()
    {
        if (!Mage::getStoreConfigFlag('queue/general/enabled')) {
            return;
        }

        $lockName = 'queue_worker';
        $lock = Mage::getModel(Mage::getStoreConfig('queue/manager/lock_model'));
        $lock->setTimeout(600);
        if (!$lock->lock($lockName)) {
            return;
        }

        $job = Mage::getModel('queue/job');
        $job->getResource()->beginTransaction();
        $maxJobs = (int)Mage::getStoreConfig('queue/general/max_jobs');

        try {
            $jobs = Mage::getModel('queue/job')
                ->getCollection()
                ->addPendingFilter();

            $jobs->getSelect()
                ->limit($maxJobs);

            foreach ($jobs as $job) {
                $job->dequeue();

                $history = Mage::getModel('queue/job_history')
                    ->getCollection()
                    ->addJobFilter($job->getId());
                if ($history->count()-1 >= $job->getNumRetries()) {
                    $this->logJobFailed($job, new Exception('Maximum number of job retries reached'));
                    continue;
                }

                try {
                    $handler = unserialize($job->getHandler());
                    $handler->perform();
                    $this->logJobSuccess($job);
                } catch (Made_Queue_Model_Job_RecoverableException $e) {
                    $job->enqueue();
                    $this->logJobRecoverableError($job, $e);
                } catch (Exception $e) {
                    $this->logJobFailed($job, $e);
                }
            }

            $job->getResource()->commit();
            $lock->unlock($lockName);
        } catch (Exception $e) {
            $job->getResource()->rollBack();
            $lock->unlock($lockName);
            // Since we throw it, Magento's cron error log can catch it
            throw $e;
        }
    }

    /**
     * Garbage collect the queue in mysql, it might grow like crazy
     *
     * @param Varien_Event_Observer $observer
     */
    public function gc()
    {
        $resource = Mage::getResourceModel('queue/job');
        $resource->gc();
    }

}