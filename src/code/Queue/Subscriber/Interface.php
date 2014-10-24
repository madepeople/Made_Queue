<?php


interface Made_Queue_Subscriber_Interface
{
    public function handleMessage(Made_Queue_Model_Message $message, Made_Queue_MessageBroker_Interface $broker);
}