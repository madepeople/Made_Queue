<?php

class JobTest extends PHPUnit_Framework_TestCase
{
    public function testPublishConsume()
    {
        $job = new Made_Queue_Model_Job_Example();
        $job->setMessage('Hello, world!');
        $job->defer();

        $worker = new Made_Queue_Model_Worker();
        $worker->executeJobs();
    }
}