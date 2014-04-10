<?php

class Made_Queue_Model_Resource_Manager
    extends Mage_Core_Model_Resource_Db_Abstract
{
    public function _construct()
    {
        $this->_init('queue/job', 'job_id');
    }

    public function getPendingJobs()
    {
        $read = $this->getReadConnection();
        $select = $read->select()
            ->forUpdate()
            ->from($this->getMainTable(), array('job_id', 'handler'))
            ->where('finished_at IS NULL')
            ->where('failed_at IS NULL');

        return $read->fetchAll($select);
    }

    public function recordJobFinishedAt($jobId, $datetime)
    {
        $write = $this->_getWriteAdapter();
        $write->update($this->getMainTable(),
            array('finished_at' => $datetime),
            'job_id = ' . (int)$jobId
        );
    }

    public function recordJobFailedAt($jobId, Exception $e, $datetime)
    {
        $write = $this->_getWriteAdapter();
        $write->update($this->getMainTable(),
            array(
                'error' => $e->getMessage(),
                'failed_at' => $datetime
            ),
            'job_id = ' . (int)$jobId
        );
    }
}