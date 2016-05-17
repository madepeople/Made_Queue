<?php


/**
 * This messagebroker will queue the messages in the database but not deliver them
 *
 */
class Made_Queue_MessageBroker_Queue extends Made_Queue_MessageBroker_Abstract implements Made_Queue_MessageBroker_Interface
{
    
    public function publishMessage(Made_Queue_Model_Message $message)
    {
        $this->_publishMessageInDatabase($message);
    }

}