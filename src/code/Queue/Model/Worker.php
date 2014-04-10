<?php
/**
 * Queue Management Job Worker
 *
 * @author jonathan@madepeople.se
 */
class Made_Queue_Model_Worker
{

    /**
     * Fetch jobs from the queue and execute them
     */
    public function executeJobs()
    {
        $manager = Mage::getModel('queue/manager');
        $manager->getResource()->beginTransaction();

        try {
            $jobs = $manager->getPendingJobs();

            foreach ($jobs as $job) {
                try {
                    $job['handler']->perform();
                    $manager->recordJobFinishedAt($job, new Zend_Db_Expr('NOW()'));
                } catch (Exception $e) {
                    if (!$manager->incrementFailedAttempts($job)) {
                        $manager->recordJobFailedAt($job, $e, new Zend_Db_Expr('NOW()'));
                    }
                }
            }

            $manager->getResource()->commit();
        } catch (Exception $e) {
            $manager->getResource()->rollBack();

            // Since we throw it, Magento's cron error log can catch it
            throw $e;
        }
    }
}
