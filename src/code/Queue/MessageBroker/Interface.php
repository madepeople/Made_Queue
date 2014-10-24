<?php


interface Made_Queue_MessageBroker_Interface
{

    /** Broker a message
     *
     * This method tells the broker to broker a message, however, it does not
     * require the message to be sent to the subscribers right away. Storing the
     * message in the database is therefore a perfectly valid way of publishing
     * messages.
     *
     * Message delivery is not guaranteed to happen before this method
     * returns.
     *
     * @returns null
     */
    public function publishMessage(Made_Queue_Model_Message $message);

}