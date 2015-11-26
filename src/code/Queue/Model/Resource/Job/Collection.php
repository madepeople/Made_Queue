<?php

/**
 * The job collection, son
 *
 * @author jonathan@madepeople.se
 */
class Made_Queue_Model_Resource_Job_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract
{

    public function _construct()
    {
        $this->_init('queue/job');
    }

    /**
     * Filter to only select pending jobs, which are ones that have entries
     * in the job_queue table
     *
     * @return $this
     */
    public function addPendingFilter()
    {
        $this->getSelect()
            ->join(array('jq' => $this->getTable('queue/job_queue')),
                'main_table.job_id = jq.job_id');
        return $this;
    }

    /**
     * Filters items per queue name
     *
     * @param $queue
     * @return $this
     */
    public function addQueueFilter($queue)
    {
        $this->getSelect()
            ->where('main_table.queue = ?', $queue);
        return $this;
    }

}