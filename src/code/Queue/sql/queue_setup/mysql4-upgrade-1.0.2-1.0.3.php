<?php

/**
 * Increase column sizes so we can keep gigantic data amounts, also fix default
 * value for a timestamp field
 */

$this->startSetup();

$this->run("ALTER TABLE {$this->getTable('made_queue_message')} CHANGE `status_changed_at` `status_changed_at` TIMESTAMP COMMENT 'Status changed at'");
$this->run("ALTER TABLE {$this->getTable('made_queue_message')} CHANGE `parameters` `parameters` LONGTEXT  CHARACTER SET latin1  COLLATE latin1_swedish_ci  NULL  COMMENT 'Parameters in JSON format'");
$this->run("ALTER TABLE {$this->getTable('made_queue_log')} CHANGE `message_parameters` `message_parameters` LONGTEXT  CHARACTER SET latin1  COLLATE latin1_swedish_ci  NULL  COMMENT 'Message parameters in JSON format'");

$this->endSetup();
