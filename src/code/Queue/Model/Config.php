<?php

/**
 */
class Made_Queue_Model_Config extends Varien_Simplexml_Config
{
    /**
     * Id for config cache
     */
    const CACHE_ID  = 'config_queue';

    /**
     * Tag name for config cache
     */
    const CACHE_TAG = 'CONFIG_QUEUE';

    /**
     * Constructor
     * Initializes XML for this configuration
     * Local cache configuration
     *
     * @param string|Varien_Simplexml_Element|null $sourceData
     */
    public function __construct($sourceData = null)
    {
        parent::__construct($sourceData);

        $canUseCache = Mage::app()->useCache('config');
        if ($canUseCache) {
            $this->setCacheId(self::CACHE_ID)
                ->setCacheTags(array(Mage_Core_Model_Config::CACHE_TAG, self::CACHE_TAG))
                ->setCacheChecksum(null)
                ->setCache(Mage::app()->getCache());

            if ($this->loadCache()) {
                return;
            }
        }

        // Load data of config files queue.xml
        $config = Mage::getConfig()->loadModulesConfiguration('queue.xml');
        if ($node = $config->getNode('queue')) {
            // TODO: What if node is not set?
            $this->setXml($node);
        }

        if ($canUseCache) {
            $this->saveCache();
        }

    }

    /** Get all subscribers
     *
     * Subscribers are defined in `etc/queue.xml` under "config/queue/subscriber/$name/$subname" where $subscriberId === "$name/$subname".
     *
     * Example of a modules `etc/queue.ini`:

     <config>
        <queue>
            <subscriber>
                <my_module>
                    <product_create>
                        <class>My_Module_Product_Created_Task</class>
                    </product_create>
                </my_module>
            </subscriber>
        </queue>
    </config>

    */

    public function getSubscribers()
    {
        $subscribers = array();

        $node = $this->getNode("subscriber");

        if (!empty($node)) {

            foreach ($node->children() as $module) {
                foreach ($module->children() as $subscriber) {
                    $definition = $subscriber->asArray();

                    if (!array_key_exists('class', $definition)) {
                        throw new Mage_Exception("Child 'class' not found in import definition for {$taskId}.");
                    }
            
                    $class = $definition['class'];
            
                    if (!class_exists($class)) {
                        throw new Mage_Exception("Class {$class} not found.");
                    }
            
                    $subscribers[] = new $class();
                }
            }
        }

        return $subscribers;

    }

}
