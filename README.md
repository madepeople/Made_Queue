# Magento Queue Job Manager


Sometimes we need to queue things without messing around with queue servers because we have a smaller scale. Sometimes we have a larger scale but don't want the overhead of extra services, and the things we queue are few. Regardless, this module is a database-based queue manager that can be used to asynchronously flush things like order exports, reports generation, and so on.

## Features

* Runs via crontab as an ordinary Magento cron job
* Uses locking to prevent parallell queue execution
* Lock classes are pluggable, the default one uses directories doesn't scale
* Can be enabled/disabled in System / Configuration
* Supports batches (default 100 jobs per run)
* Uses **traits**, which lets you plug deferrability into your classes of choice
* Queue namespacing
* Jobs that fail with a recoverable error (such as an I/O error), can be retried until succesful

## Usage

First, install the module. Second, enable the standard Magento cron job. After that, all you need to do is have a look at the Example class in the Model/Job directory:

```php
<?php

/**
 * Example job
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
     * Just a setter
     *
     * @param $message
     */
    public function setMessage($message)
    {
        $this->_message = $message;
    }

    /**
     * String representation of object
     
     * @return string the string representation of the object or null
     */
    public function serialize()
    {
        return serialize($this->_message);
    }

    /**
     * The string representation of the object.
     *
     * @return void
     */
    public function unserialize($serialized)
    {
        $this->_message = unserialize($serialized);
    }
}
```

Key in this code is "use Deferrable;" and "perform()". The trait adds the defer() method to the class which when called creates a job and adds it to the queue for execution. The queue manager runs the "perform()" method on an unserialized version of the class. A class can defer itself, like this:

```php
<?php

$job = new Made_Queue_Model_Job_Example();
$job->setMessage('lol');
$job->defer();
```

There are two optional arguments for defer() - $queue and $numRetries. $queue is used for queue namespacing. $numRetries determines how many times we should retry a recoverable error (default 1).

## Error Management
Some jobs fail fatally, some fail temporarily. To differentiate between these two there is an Made_Queue_Model_Job_RecoverableException class your job can throw if it needs to be retried. All other exceptions will be treated as fatal and these jobs won't be retried.


## Future

The future might implement a pluggable queue daemon interface for us, and just keep the ease of queueability in this module