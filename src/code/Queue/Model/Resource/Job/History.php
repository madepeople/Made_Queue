<?php

/**
 * History resource model
 *
 * @author jonathan@madepeople.se
 */
class Made_Queue_Model_Resource_Job_History
    extends Mage_Core_Model_Resource_Db_Abstract
{

    /**
     * Use the made_queue_job table
     */
    public function _construct()
    {
        $this->_init('queue/job_history', 'history_id');
    }

    /**
     * Garbage collect directly in the database
     */
    public function gc()
    {
        $table = $this->getMainTable();
        $query = "DELETE FROM {$table} WHERE created_at < NOW() - INTERVAL 1 WEEK";
        $this->_getWriteAdapter()
            ->query($query);
    }

}