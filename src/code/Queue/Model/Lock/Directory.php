<?php

/**
 * Directory-based locking mechanism
 *
 * @author jonathan@madepeople.se
 */
class Made_Queue_Model_Lock_Directory
    implements Made_Queue_Model_Lock_Interface
{

    private $_suffix = '.lock';
    private $_prefix = '';
    private $_timeout = 3600;

    /**
     * Get the locking base directory path
     *
     * @return string
     * @throws Exception
     */
    protected function _getLockBasedir()
    {
        $baseDir = Mage::getBaseDir('var') . DS . 'locks/queue';
        if (!is_dir($baseDir)) {
            $varienIo = new Varien_Io_File;
            $result = $varienIo->mkdir($baseDir, 0777, true);
            if ($result === false) {
                throw new Exception("Couldn't create basedir for locking: $baseDir");
            }
        }

        if (!is_writable($baseDir)) {
            throw new Exception("Locking basedir not writable by web server: $baseDir");
        }

        return $baseDir;
    }

    /**
     * Get the directory used for locking
     *
     * @param $name
     * @return string
     */
    protected function _getLockDir($name)
    {
        $baseDir = $this->_getLockBasedir();
        $lockDir = join(DS, array(
            $baseDir,
            $this->_prefix,
            $name . $this->_suffix
        ));
        return $lockDir;
    }

    /**
     * Set the lock prefix
     *
     * @param string $prefix
     * @return void
     */
    public function setPrefix($prefix)
    {
        $this->_prefix = $prefix;
    }

    /**
     * Setter for the lock timeout. The lock is valid unless unlocked until
     * this timeout has been reached
     *
     * @param int $timeout
     * @return void
     * @throws Exception
     */
    public function setTimeout($timeout)
    {
        $timeout = (int)$timeout;
        if ($timeout < 1) {
            throw new Exception('Positive integer required as timeout');
        }
        $this->_timeout = $timeout;
    }

    /**
     * Locks the resource by name. Throws Exception if a resource couldn't be
     * accessed for instance. If I/O works and the lock already exists, false
     * is returned
     *
     * @param string $name
     * @return boolean  true if success, false if lock exists
     * @throws Exception
     */
    public function lock($name)
    {
        $lockDir = $this->_getLockDir($name);
        $this->test($name);

        if (!@mkdir($lockDir)) {
            throw new Exception("Error creating lock dir: $lockDir");
        }

        return true;
    }

    /**
     * Unlocks a previously locked resource. Return true if success, false if
     * lock doesn't exist
     *
     * @param string $name
     * @return boolean
     * @throws Exception
     */
    public function unlock($name)
    {
        $lockDir = $this->_getLockDir($name);
        if (is_dir($lockDir)) {
            if (rmdir($lockDir) === false) {
                throw new Exception("Couldn't release lock dir: $lockDir");
            }
        }
        return true;
    }

    /**
     * Test the existence of a lock. Return true if lock exists, otherwise false
     *
     * @param string $name
     * @return boolean
     * @throws Exception
     */
    public function test($name)
    {
        $lockDir = $this->_getLockDir($name);
        if (is_dir($lockDir)) {
            if (time()-filemtime($lockDir) > $this->_timeout) {
                if (rmdir($lockDir) === false) {
                    throw new Exception("Couldn't remove timed out lock dir: $lockDir");
                }
                return false;
            }
            return true;
        }
        return false;
    }

}
