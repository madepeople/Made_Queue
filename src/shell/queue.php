<?php
/** Made_Queue script for messages and message queue
 *
 * This script must be run from the magento base directory.
 */


error_reporting(E_ALL);
ini_set('display_errors', true);

require_once 'shell/abstract.php';

/**
 */
class Made_Shell_Queue extends Mage_Shell_Abstract
{

    public function getMessageBroker()
    {
        $broker = Mage::helper('queue')->getBackendMessageBroker();
        return $broker;
    }

    public function getMessage()
    {
        if (count($_SERVER['argv']) < 3) {
            echo "Parameter `name` required\n";
            echo $this->usageHelp();
            die(1);
        }

        $action = $_SERVER['argv'][1];
        $name = $_SERVER['argv'][2];
            
        $parameters = array_merge(array(), $this->_args);
        unset($parameters[$name]);
        unset($parameters[$action]);
            

        return $message = Mage::getModel('queue/message')
            ->setName($name)
            ->setParameters($parameters);
    }

    public function run()
    {
        if (count($_SERVER['argv']) < 2) {
            echo "Invalid\n";
            return -1;
        }

        $action = $_SERVER['argv'][1];

        switch ($action) {
        case 'queue':
            $message = $this->getMessage();
            $message->save();

            if ($this->getArg('verbose')) {
                echo "Message queued: {$message}.\n";
            }

            break;
        case 'publish':
            $message = $this->getMessage();
            $broker = $this->getMessageBroker();
            $messageId = $broker->publishMessage($message);

            if ($this->getArg('verbose')) {
                echo "Message published: {$message}.\n";
            }

            break;
        case 'deliver':
            $broker = $this->getMessageBroker();

            $limit = $this->getArg('limit');

            if ($limit === false) {
                $limit = 100;
            } else {
                $limit = (int)$limit;
            }

            $counter = $broker->deliverMessages($limit);

            echo "Delivered {$counter} message" . ($counter === 1 ? '' : 's') . ".\n";
            break;
        case 'list':
            // Handled below, for '--list' arguments
            break;

        default:
            echo "No such action - '{$action}'\n";
            return -1;
        }

        if ($this->getArg('list')) {
            $this->listMessages();
        }
            
    }

    public function listMessages()
    {
        $messages = Mage::getModel('queue/message')->getCollection()->setOrder('created_at');

        $numMessages = count($messages);
        if ($numMessages === 0) {
            echo "No messages.", "\n";
        } else {
            echo "Messages (" . count($messages) . ")\n";
            $formatString = "%-19s %-30s %-8s %s\n";
            printf($formatString, "Created at", "Name", "Status", "Parameters");
            foreach ($messages as $message) {
                printf($formatString, $message->getData('created_at'), $message->getName(), $message->getData('status'), $message->getParametersAsJson());
            }
        }
    }

    /** Retrieve Usage Help Message
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage: php -f shell/queue.php <action> [OPTIONS]

Actions:

  help                          This help
  list                          List messages
  queue <name> [--key value]*   Queue a message
  deliver                       Deliver messages

Flags:

--verbose                       Enable Stdout logging
--limit                         Maximum number of messages to deliver(default 100)
--list                          List messages after action

USAGE;
    }
}

$shell = new Made_Shell_Queue();
$shell->run();
