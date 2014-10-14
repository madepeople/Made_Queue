<?php

class Made_Queue_Model_Resource_Log extends Mage_Core_Model_Resource_Db_Abstract
{

    public function _construct()
    {
        $this->_init('queue/log', 'log_id');
    }

}
