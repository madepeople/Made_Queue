<?php

class Made_Queue_Model_Resource_Message_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{

    public function _construct()
    {
        $this->_init('queue/message', 'message_id');
    }

    /**
     * Calls afterLoad on all items since that is required for parameters to work
     */
    protected function _afterLoadData()
    {
        foreach ($this->getItems() as $item) {
            $item->afterLoad();
        }

        return parent::_afterLoadData();
    }

}
