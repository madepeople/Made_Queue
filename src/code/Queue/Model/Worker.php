<?php
/**
 * Queue Management Job Worker
 *
 * @author jonathan@madepeople.se
 */
class Made_Queue_Model_Worker
{
    protected $_lockPrefix = 'queue_worker';
    protected $_lockDirTimeout = 600; // 15 minute default lockdir timeout
    protected $_lockDirSuffix = '_navision.lock';

    /**
     * Fetch jobs from the queue and execute them
     */
    public function executeJobs()
    {
        if (!Mage::getStoreConfigFlag('queue/general/enabled')) {
            return;
        }

        if (!$this->_createLock()) {
            return;
        }

        $manager = Mage::getModel('queue/manager');
        $manager->getResource()->beginTransaction();
        $maxJobs = 100;

        try {
            $i = 0;
            $jobs = $manager->getPendingJobs();

            foreach ($jobs as $job) {
                if ($i >= $maxJobs) {
                    break;
                }
                try {
                    $job['handler']->perform();
                    $manager->recordJobFinishedAt($job, new Zend_Db_Expr('NOW()'));
                } catch (Exception $e) {
                    if (!$manager->incrementFailedAttempts($job)) {
                        $manager->recordJobFailedAt($job, $e, new Zend_Db_Expr('NOW()'));
                    }
                }
                $i++;
            }

            $manager->getResource()->commit();
            $this->_releaseLock($this->_lockPrefix);
        } catch (Exception $e) {
            $manager->getResource()->rollBack();
            $this->_releaseLock($this->_lockPrefix);
            // Since we throw it, Magento's cron error log can catch it
            throw $e;
        }
    }

    /**
     * Get the locking base directory path
     *
     * @return string
     * @throws Exception
     */
    protected function _getLockBasedir()
    {
        $baseDir = Mage::getBaseDir('var') . DS . 'locks/nav';
        if (!is_dir($baseDir)) {
            if (!mkdir($baseDir, 0777)) {
                throw new Exception("Couldn't create basedir for locking: $baseDir");
            }
        }

        if (!is_writable($baseDir)) {
            throw new Exception("Locking basedir not writable by webserver: $baseDir");
        }

        return $baseDir;
    }

    /**
     * Protected integration execution with a lock dir
     *
     * @param string $prefix  Lock dir prefix
     * @throws Exception
     */
    protected function _createLock($prefix = null)
    {
        if ($prefix === null) {
            $prefix = $this->_lockPrefix;
        }

        $baseDir = $this->_getLockBasedir();
        $lockDir = $baseDir . DS . $prefix . $this->_lockDirSuffix;
        if (is_dir($lockDir)) {
            if (time()-filemtime($lockDir) > $this->_lockDirTimeout) {
                if (rmdir($lockDir) === false) {
                    throw new Exception("Couldn't remove timed out lock dir: $lockDir");
                }
            }
        }

        if (!@mkdir($lockDir)) {
            throw new Exception("Error creating lock dir, integration probably already running: $lockDir");
        }

        return true;
    }

    /**
     * Releases the lock
     *
     * @param string $prefix  Lock dir prefix
     */
    protected function _releaseLock($prefix = null)
    {
        if ($prefix === null) {
            $prefix = $this->_lockPrefix;
        }

        $baseDir = $this->_getLockBasedir();
        $lockDir = $baseDir . DS . $prefix . $this->_lockDirSuffix;
        if (is_dir($lockDir)) {
            if (rmdir($lockDir) === false) {
                throw new Exception("Couldn't release lock dir: $lockDir");
            }
        }
    }
}
