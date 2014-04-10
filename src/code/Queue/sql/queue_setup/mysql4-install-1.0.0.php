<?php

$this->startSetup();

$this->run("
DROP TABLE IF EXISTS {$this->getTable('made_queue_job')};
CREATE TABLE {$this->getTable('made_queue_job')} (
    job_id INT UNSIGNED NOT NULL PRIMARY KEY auto_increment,
    queue VARCHAR(255) NOT NULL DEFAULT 'default',
    handler TEXT,
    error TEXT,
    failed_attempts SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    failed_at DATETIME NULL,
    finished_at DATETIME NULL,
    created_at DATETIME NOT NULL,
) ENGINE=MyISAM;
");

$this->endSetup();