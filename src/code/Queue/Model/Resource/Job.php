<?php

/**
 * Resource model for the queue manager, controls the database entries related
 * to jobs and their statuses
 *
 * @author jonathan@madepeople.se
 */
class Made_Queue_Model_Resource_Job
    extends Mage_Core_Model_Resource_Db_Abstract
{

    /**
     * Use the made_queue_job table
     */
    public function _construct()
    {
        $this->_init('queue/job', 'job_id');
    }

    /**
     * Automatically set the created_at datetime. Why not a timestamp? Dunno
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Mage_Core_Model_Resource_Db_Abstract|void
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        if (!$object->getCreatedAt()) {
            $object->setCreatedAt(new Zend_Db_Expr('NOW()'));
        }
    }

    /**
     * Enqueue this job, which means we want to run it
     *
     * @param int $jobId
     */
    public function enqueue($jobId)
    {
        $table = $this->getTable('queue/job_queue');
        $this->_getWriteAdapter()->insert($table, array(
            'job_id' => $jobId
        ));
    }

    /**
     * Dequeue the job. Typically used when the job has been executed, or for
     * regrets
     *
     * @param int $jobId
     */
    public function dequeue($jobId)
    {
        $table = $this->getTable('queue/job_queue');
        $where = $this->_getWriteAdapter()->quoteInto('job_id = ?', $jobId);
        $this->_getWriteAdapter()->delete($table, $where);
    }

    /**
     * Garbage collect directly in the database
     */
    public function gc()
    {
        $table = $this->getMainTable();
        $query = "DELETE FROM {$table} WHERE created_at < NOW() - INTERVAL 1 DAY";
        $this->_getWriteAdapter()
            ->query($query);
    }
}
