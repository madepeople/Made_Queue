<?php

class Made_Queue_Model_Resource_Message extends Mage_Core_Model_Resource_Db_Abstract
{

    public function _construct()
    {
        $this->_init('queue/message', 'message_id');
    }

}
