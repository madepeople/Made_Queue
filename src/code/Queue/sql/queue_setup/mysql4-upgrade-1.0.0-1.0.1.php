<?php

/**
 * Create the history table which represents job runs, and the job_queue table
 * that serves the purpose of an actual queue. It's cleaned when a job has run
 * and filled when there are things to run.
 *
 * @author jonathan@madepeople.se
 */

$this->startSetup();

$this->run("
ALTER TABLE {$this->getTable('made_queue_job')} DROP COLUMN error;
ALTER TABLE {$this->getTable('made_queue_job')} DROP COLUMN failed_attempts;
ALTER TABLE {$this->getTable('made_queue_job')} DROP COLUMN failed_at;
ALTER TABLE {$this->getTable('made_queue_job')} DROP COLUMN finished_at;
ALTER TABLE {$this->getTable('made_queue_job')} ADD status ENUM('PENDING', 'DONE', 'FAILED') NOT NULL DEFAULT 'PENDING';
ALTER TABLE {$this->getTable('made_queue_job')} ADD num_retries INT UNSIGNED NOT NULL DEFAULT 1;
ALTER TABLE {$this->getTable('made_queue_job')} ENGINE=InnoDB;

CREATE TABLE {$this->getTable('made_queue_job_queue')} (
  job_id INT UNSIGNED NOT NULL,
  FOREIGN KEY (job_id) REFERENCES {$this->getTable('made_queue_job')}(job_id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE {$this->getTable('made_queue_job_history')} (
  history_id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  job_id INT UNSIGNED NOT NULL,
  status ENUM('SUCCESS', 'RECOVERABLE_ERROR', 'FATAL_ERROR') NOT NULL,
  message TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (job_id) REFERENCES {$this->getTable('made_queue_job')}(job_id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;
");

$this->endSetup();