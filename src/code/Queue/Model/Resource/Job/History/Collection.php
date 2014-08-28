<?php

/**
 * @author jonathan@madepeople.se
 */
class Made_Queue_Model_Resource_Job_History_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract
{

    public function _construct()
    {
        $this->_init('queue/job_history');
    }

    /**
     * Filter the collection on a specific job, to find out how many
     * times it has actually run
     *
     * @param int $jobId
     * @return $this
     */
    public function addJobFilter($jobId)
    {
        $this->getSelect()
            ->join(array('j' => $this->getTable('queue/job')),
                'main_table.job_id = j.job_id')
            ->where('j.job_id = ?', $jobId);
        return $this;
    }

}