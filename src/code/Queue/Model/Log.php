<?php


class Made_Queue_Model_Log extends Mage_Core_Model_Abstract
{

    /** Message parameters
     *
     * @var array
     */
    private $_messageParameters = array();

    /** Exception information
     *
     * Will contain 'name', 'message' and 'backtrace' from an exception or be NULL
     *
     * @var array
     */
    private $_exceptionInformation = null;

    public function _construct()
    {
        parent::_construct();
        $this->_init('queue/log');
    }

    /** Set message that should be logged
     *
     * This is the prefered way of logging a Made_Queue_Model_Message.
     *
     * @param $message Made_Queue_Model_Message
     *
     * @returns Made_Queue_Model_Log $this
     */
    public function setMessage(Made_Queue_Model_Message $message)
    {
        $this->setData('message_name', $message->getName());

        $this->_messageParameters = $message->getParameters();

        return $this;
    }

    public function setSubscriber(Made_Queue_Subscriber_Interface $subscriber)
    {
        $this->setData('subscriber', get_class($subscriber));

        return $this;
    }

    public function setStatus($status)
    {
        $this->setData('status', $status);

        return $this;
    }

    public function setException(Exception $exception)
    {
        $this->_exceptionInformation = array(
            'class' => get_class($exception),
            'message' => $exception->getMessage(),
            'backtrace' => $exception->getTrace(),
        );

        return $this;
    }
    

    protected function _beforeSave()
    {

        // Json encode message parameters
        $this->setData('message_parameters', $this->getMessageParametersAsJson());

        // Json encode exception data
        $this->setData('exception_information', Mage::helper('core')->jsonEncode($this->_exceptionInformation));

        return parent::_beforeSave();
    }

    protected function _afterLoad()
    {
        $this->_messageParameters = Mage::helper('core')->jsonDecode($this->getData('message_parameters'));
        $this->_exceptionInformation = Mage::helper('core')->jsonDecode($this->getData('exception_information'));

        return parent::_afterLoad();
    }

    public function __toString()
    {
        return "'{$this->getData('message_name')} {$this->getMessageParametersAsJson()}'";
    }

    public function getMessageParametersAsJson()
    {
        return count($this->_messageParameters) === 0 ? '{}' : Mage::helper('core')->jsonEncode($this->_messageParameters);
    }

}