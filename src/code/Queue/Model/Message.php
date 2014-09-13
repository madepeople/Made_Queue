<?php

/**
 * A queue message
 *
 * A message has a name, parameters and a delivery_status. The parameters are
 * serialized/deserialized to and from json, not using PHP serializing.
 *
 * Some messages will want to have extra functionality attached to them.
 * An example of this is "product created" messages which may want to have
 * a getProduct() function. Since magento doesn't provide this feature messages
 * works in a different way. Each message can have one, and only one, helper class.
 *
 */
class Made_Queue_Model_Message extends Mage_Core_Model_Abstract
{

    /**
     * Status for when a message is waiting for to be delivered
     *
     * @var string
     */
    const STATUS_DELIVERY_PENDING = 'DELIVERY_PENDING';

    /**
     * Status for when a message is being delivered
     *
     * @var string
     */
    const STATUS_DELIVERY_IN_PROGRESS = 'DELIVERY_IN_PROGRESS';

    /**
     * Message parameters
     */
    private $_parameters = array();

    public function _construct()
    {
        parent::_construct();
        $this->_init('queue/message');
    }

    public function setStatus($status)
    {
        $this->setData('status', $status);
        return $this;
    }

    public function getStatus()
    {
        return $this->getData('status');
    }

    public function setParameters(array $parameters)
    {
        $this->_parameters = $parameters;

        return $this;
    }

    public function getParameters()
    {
        return $this->_parameters;
    }

    public function getParameter($id, $default=null)
    {
        if (array_key_exists($id, $this->_parameters)) {
            return $this->_parameters[$id];
        } else if (func_num_args() === 2) {
            return $default;
        } else {
            throw new Exception("Parameter {$id} not set and no default value supplied");
        }
    }

    public function setName($name)
    {
        $this->setData('name', $name);

        return $this;
    }

    public function getName()
    {
        return $this->getData('name');
    }

    protected function _beforeSave()
    {

        // Json encode parameters
        $this->setData('parameters', $this->getParametersAsJson());

        // Update status_changed_at if status changed and the object isn't new
        if (!$this->isObjectNew()) {
            if ($this->getOriginalData('status') !== $this->getData('status')) {
                $this->setData('status_changed_at', Varien_Date::now());
            }
        }

        return parent::_beforeSave();
    }

    protected function _afterLoad()
    {
        $this->_parameters = Mage::helper('core')->jsonDecode($this->getData('parameters'));
        return parent::_afterLoad();
    }


    public function getParametersAsJson()
    {
        return count($this->_parameters) === 0 ? '{}' : Mage::helper('core')->jsonEncode($this->_parameters);
    }

    public function __toString()
    {
        return "'{$this->getName()} {$this->getParametersAsJson()}'";
    }

}