<?php

/**
 *
 * - Before delivery to a subscriber a Made_Queue_MessageBroker_Backend::STATUS_DELIVERY_START will be logged
 * - If no Exception was caught a Made_Queue_MessageBroker_Backend::STATUS_DELIVERY_SUCCESS will be logged
 * - If an Exception is caught a Made_Queue_MessageBroker_Backend::STATUS_DELIVERY_ERROR will be logged
 *
 */
abstract class Made_Queue_MessageBroker_Abstract implements Made_Queue_MessageBroker_Interface
{

    const STATUS_DELIVERY_STARTED = 'started';

    const STATUS_DELIVERY_SUCCESS = 'success';

    const STATUS_DELIVERY_ERROR = 'error';

    /**
     * List of subscribers
     *
     * @var array
     */
    private $_subscribers = array();

    /**
     * Options with default values
     *
     * @var array
     */
    private $_options = array(
        'logLevel' => Zend_Log::DEBUG,
    );

    /**
     * Create a new Backend Broker
     *
     * @param $options array Map of options or null, see Made_Queue_MessageBroker_Backend::$_options
     */
    public function __construct(array $options=null)
    {
        if ($options !== null) {
            foreach ($this->_options as $key=>$default) {
                if (array_key_exists($key, $options)) {
                    $_options = $options[$key];
                }
            }
        }
    }

    public function getOption($id, $default=null)
    {
        if (array_key_exists($id, $this->_options)) {
            return $this->_options[$id];
        } else if (func_num_args() === 2) {
            return $default;
        } else {
            throw new Exception("Option {$id} not set and no default value supplied");
        }
    }
    
    /**
     * Add a subscriber
     *
     * The order subscribers are added in does not determine the order they will
     * recieve a message.
     *
     * @returns Made_Queue_MessageBroker_Backend $this
     */
    public function addSubscriber($subscriber)
    {
        $this->_subscribers[] = $subscriber;

        return $this;
    }

    /**
     * Get list of subscribers for a message
     *
     * @returns array Unsorted list of subscribers that subscribes to a message
     */
    public function getSubscribersForMessage(Made_Queue_Model_Message $message)
    {
        return $this->_subscribers;
    }

    /**
     * Log delivery
     *
     * @param $logMessage Made_Queue_Model_Log The log message that should be logged
     * @param $level int Zend_Log level. If the level is higher than the 'logLevel' option the log message will be discarded
     *
     */
    protected function _log(Made_Queue_Model_Log $logMessage, $level=Zend_Log::DEBUG)
    {
        if ($level !== null && $level > $this->getOption('logLevel', Zend_Log::DEBUG)) {
            return;
        } else {
            $logMessage->save();
        }

    }

    /**
     * Publish a message in the database
     *
     * @param $message Made_Queue_Model_Message A message that currently isn't stored in the database
     *
     * @returns null
     */
    protected function _publishMessageInDatabase(Made_Queue_Model_Message $message)
    {
        $message->setStatus(Made_Queue_Model_Message::STATUS_DELIVERY_PENDING);
        $message->save();
    }

}