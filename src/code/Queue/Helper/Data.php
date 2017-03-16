<?php


class Made_Queue_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Get Made_Queue config instance
     *
     * @return Made_Qeueu_Model_Config
     */
    final public function getConfig()
    {
        return Mage::getSingleton('queue/config');
    }

    final public function getBackendMessageBroker()
    {
        $broker = new Made_Queue_MessageBroker_Backend();

        foreach ($this->getConfig()->getSubscribers() as $subscriber) {
            $broker->addSubscriber($subscriber);
        }

        return $broker;
    }

    final public function getFrontendMessageBroker()
    {
        $broker = new Made_Queue_MessageBroker_Frontend();

        foreach ($this->getConfig()->getSubscribers() as $subscriber) {
            $broker->addSubscriber($subscriber);
        }

        return $broker;
    }

}