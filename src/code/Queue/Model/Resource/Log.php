<?php

class Made_Queue_Model_Resource_Log extends Mage_Core_Model_Resource_Db_Abstract
{

    public function _construct()
    {
        $this->_init('queue/log', 'log_id');
    }

    /**
     * Garbage collect directly in the database
     */
    public function gc()
    {
        $table = $this->getMainTable();
        $query = "DELETE FROM {$table} WHERE created_at < NOW() - INTERVAL 1 MONTH";
        $this->_getWriteAdapter()
            ->query($query);
    }
}
