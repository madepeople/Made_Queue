<?php

/**
 * Create the message table in database
 *
 * @author daniel@madepeople.se
 */

$this->startSetup();

$messageTableName = $this->getTable('made_queue_message');
$queueLogTableName = $this->getTable('made_queue_log');

// Valid statuses for a message
$statusDeliveryPending = Made_Queue_Model_Message::STATUS_DELIVERY_PENDING;
$statusDeliveryInProgress = Made_Queue_Model_Message::STATUS_DELIVERY_IN_PROGRESS;

/** Create message table
 */
$sql =<<<SQL
CREATE TABLE {$messageTableName} (
  message_id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'Message id',
  name  VARCHAR(512) NOT NULL COMMENT 'Name',
  parameters TEXT COMMENT 'Parameters in JSON format',
  status ENUM('{$statusDeliveryPending}', '{$statusDeliveryInProgress}') NOT NULL DEFAULT '{$statusDeliveryPending}' COMMENT 'Status',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created at',
  status_changed_at TIMESTAMP DEFAULT 0 COMMENT 'Status changed at'
) ENGINE=InnoDB;
SQL;

$this->run($sql);

$statusStarted = Made_Queue_MessageBroker_Abstract::STATUS_DELIVERY_STARTED;
$statusSuccess = Made_Queue_MessageBroker_Abstract::STATUS_DELIVERY_SUCCESS;
$statusError = Made_Queue_MessageBroker_Abstract::STATUS_DELIVERY_ERROR;

/** Create queue log table
 */
$sql =<<<SQL
CREATE TABLE {$queueLogTableName} (
  log_id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'Queue Log id',
  status ENUM('{$statusStarted}', '{$statusSuccess}', '{$statusError}') COMMENT "Status",
  message_name  VARCHAR(512) NOT NULL COMMENT 'Message name',
  message_parameters TEXT COMMENT 'Message parameters in JSON format',
  subscriber VARCHAR(512) NOT NULL COMMENT 'Subscriber',
  exception_information TEXT COMMENT 'Exception information in JSON format',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created at'
) ENGINE=InnoDB;
SQL;

$this->run($sql);

$this->endSetup();
