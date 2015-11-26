<?php
/**
 * Made_Queue script that executes a named worker queue
 *
 * @author jonathan@madepeople.se
 */

error_reporting(E_ALL);
ini_set('display_errors', true);

require_once 'shell/abstract.php';

/**
 */
class Made_Shell_QueueWorker extends Mage_Shell_Abstract
{

    public function run()
    {
        if (count($_SERVER['argv']) < 2) {
            echo "Invalid\n";
            return -1;
        }

        $queue = trim($_SERVER['argv'][1]);
        if (empty($queue)) {
            echo "Invalid\n";
            return -1;
        }
        $worker = Mage::getModel('queue/worker');
        $worker->executeJobs($queue);
    }

    /** Retrieve Usage Help Message
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage: php -f shell/queue-worker.php <queue_name>
USAGE;
    }
}

$shell = new Made_Shell_QueueWorker();
$shell->run();
