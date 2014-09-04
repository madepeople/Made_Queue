<?php

/**
 * Example job
 *
 * @author jonathan@madepeople.se
 */
class Made_Queue_Model_Job_Example implements Made_Queue_Model_Job_Interface
{
    use Made_Queue_Model_Job_Deferrable;

    protected $_message;

    /**
     * The worker runs perform on the unserialized job instance
     */
    public function perform()
    {
        echo $this->_message . "\n";
    }

    /**
     * Message setter
     *
     * @param $message
     */
    public function setMessage($message)
    {
        $this->_message = $message;
    }

    /**
     * String representation of object
     *
     * @return string the string representation of the object or null
     */
    public function serialize()
    {
        return serialize($this->_message);
    }

    /**
     * Constructs the object. The string representation of the object.
     *
     * @return void
     */
    public function unserialize($serialized)
    {
        $this->_message = unserialize($serialized);
    }
}