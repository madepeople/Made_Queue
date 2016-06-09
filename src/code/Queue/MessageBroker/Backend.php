<?php

/**
 * MessageBroker for backend and cron
 *
 * If a message is published with this broker it will be added to the database
 * _but_ it will also be delivered right away and then removed from the database.
 *
 */
class Made_Queue_MessageBroker_Backend extends Made_Queue_MessageBroker_Abstract implements Made_Queue_MessageBroker_Interface
{

    /**
     * Flag for if this broker is currently in the process of delivering a message
     *
     * @var bool
     */
    private $_isDelivering = false;
    
    /**
     * Ordered list of messages that was published with this broker, latest last.
     *
     * @var array
     */
    private $_publishedMessages = array();

    /**
     * Deliver the next available message in the message queue
     *
     * @returns bool TRUE if a message was delivered, otherwise FALSE
     */
    protected function _deliverNextInQueue()
    {
        $messageDelivered = false;

        while (($messageId = $this->_getNextPendingMessageId()) && !$messageDelivered) {

            $this->_isDelivering = true;
            if ($this->_deliverMessageById($messageId)) {

                while ($this->_deliverPublishedMessages()) {
                }

                $messageDelivered = true;
            }

            $this->_isDelivering = false;
        }

        return $messageDelivered;
    }

    /**
     * Deliver a number of messages
     *
     * @param limit int Number of messages that should be delivered, default 100
     *
     * @returns int Number of messages that were delivered
     */
    public function deliverMessages($limit=100)
    {
        $delivered = 0;
        while ($this->_deliverNextInQueue() && $delivered < $limit) {
            $delivered++;
        }
        return $delivered;
    }

    /**
     * Deliver all messages
     *
     * @returns int Number of messages delivered
     */
    public function deliverAllMessages()
    {
        $delivered = 0;
        while ($this->_deliverNextInQueue()) {
            $delivered++;
        }
        return $delivered;
    }

    /**
     * Deliver messages in $this->_publishedMessages
     *
     * @returns int Number of messages delivered
     */
    private function _deliverPublishedMessages()
    {

        $delivered = 0;

        $messages = array_values($this->_publishedMessages);
        $this->_publishedMessages = array();

        foreach ($messages as $messageId) {
            if ($this->_deliverMessageById($messageId)) {
                $delivered++;
            }
        }

        return $delivered;
    }

    /**
     * Deliver a single message
     *
     * This method doesn't check or change status of the message, it only delivers
     * it to the subscribers and logs the delivery.
     *
     * @param Made_Queue_Model_Message $message A message that is stored in the database
     *
     * @returns Made_Queue_MessageBroker_Backend $this
     */
    private function _deliverMessage(Made_Queue_Model_Message $message)
    {

        foreach ($this->getSubscribersForMessage($message) as $subscriber) {
            try {

                $this->_log(
                    Mage::getModel('queue/log')
                    ->setMessage($message)
                    ->setSubscriber($subscriber)
                    ->setStatus(self::STATUS_DELIVERY_STARTED));

                $subscriber->handleMessage($message, $this);

                $this->_log(
                    Mage::getModel('queue/log')
                    ->setMessage($message)
                    ->setSubscriber($subscriber)
                    ->setStatus(self::STATUS_DELIVERY_SUCCESS));

            } catch (Exception $e) {
                // We consider the message delivery a success here but log it with
                // error level.
                $this->_log(
                    Mage::getModel('queue/log')
                    ->setMessage($message)
                    ->setSubscriber($subscriber)
                    ->setStatus(self::STATUS_DELIVERY_ERROR)
                    ->setException($e));
            }
        }

        return $this;
    }

    /**
     * Publish a message
     *
     * The message will have been delivered before this method returns if
     * this broker is not already delivering a message, i.e this method was
     * called from a subscriber.
     *
     * If the broker is already delivering a message the message id will be added
     * to $_publishedMessages.
     *
     * @param Made_Queue_Model_Message $message A message that isn't stored in the database
     *
     * @return Made_Queue_MessageBroker_Backend $this
     */
    public function publishMessage(Made_Queue_Model_Message $message)
    {
        $this->_publishMessageInDatabase($message);

        if (!$this->_isDelivering) {
            // Start delivery if we are not already delivering messages

            $this->_isDelivering = true;

            if ($this->_deliverMessageById($message->getId())) {

                while ($this->_deliverPublishedMessages()) {

                }
            }

            $this->_isDelivering = false;
        } else {
            // Add message id to published messages
            $this->_publishedMessages[] = $message->getId();
        }

        return $this;
    }

    /**
     * Deliver a message based on it's database id
     *
     * If the message cannot be found in the database nothing will happend. This
     * method will not deliver messages that were published during the delivery
     * of $message. It will however delete the message from the database if it was
     * delivered.
     *
     * @param $messageId int Message id in the database
     *
     * @return bool TRUE if the message was delivered
     */
    protected function _deliverMessageById($messageId)
    {

        // Select message for update to prevent races
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        $resource = Mage::getModel('sales/order')->getResource();
        $tableName = $resource->getTable('queue/message');

        $write->beginTransaction();
        $selectQuery = $write->select()
                             ->forUpdate()
                             ->from($tableName, array('message_id'))
                             ->where('message_id = ?', $messageId)
                             ->where('status = ?', Made_Queue_Model_Message::STATUS_DELIVERY_PENDING)
                             ->limit(1);

        if ($write->fetchOne($selectQuery) !== false) {
            $where = $write->quoteInto('message_id = ?', $messageId);
            $write->update(
                $tableName,
                array(
                    'status' => Made_Queue_Model_Message::STATUS_DELIVERY_IN_PROGRESS,
                    'status_changed_at' => Varien_Date::now(),
                ),
                $where);

            $write->commit();

            // We are the only ones that has the right to deliver this message
            $message = Mage::getModel('queue/message')->load($messageId);
            $this->_deliverMessage($message);
            $message->delete();

            return true;
        } else {
            // Release the lock here even though nothing changed
            $write->commit();

            return false;
        }

    }

    /**
     * Get id for the next pending message from the database
     *
     * @returns int|NULL Next pending message id or NULL
     */
    private function _getNextPendingMessageId()
    {
        $message = Mage::getModel('queue/message')->getCollection()
                                                  ->addFieldToFilter('status', Made_Queue_Model_Message::STATUS_DELIVERY_PENDING)
                                                  ->setOrder('created_at')
                                                  ->setPageSize(1)
                                                  ->getFirstItem();
        return $message->getId();
            
    }

}