<?php

/**
 * Example job
 *
 * @author jonathan@madepeople.se
 */
class Made_Queue_Model_Job_Example implements Made_Queue_Model_Job_Interface
{
    use Deferrable;

    protected $_message;

    /**
     * The worker runs perform on the unserialized job instance
     */
    public function perform()
    {
        echo $this->_message . "\n";
    }

    /**
     * Setter ;)
     *
     * @param $message
     */
    public function setMessage($message)
    {
        $this->_message = $message;
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * String representation of object
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     */
    public function serialize()
    {
        return serialize($this->_message);
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Constructs the object
     * @link http://php.net/manual/en/serializable.unserialize.php
     * @param string $serialized <p>
     * The string representation of the object.
     * </p>
     * @return void
     */
    public function unserialize($serialized)
    {
        $this->_message = unserialize($serialized);
    }
}