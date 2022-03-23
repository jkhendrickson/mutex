<?php
/**
 * mutex.php
 *
 * php mutex for multi-step processing
 * @author Jeff Hendrickson JKH <jeff@hendricom.com>
 * @version 1.0
 * @package Utilities
 * example:
   require_once 'mutex.php';
	// we need to be polite, and wait as other processes
	$mutex = new Mutex("sample-mutex");
	$count = 0;
	while($mutex->isLocked()) {
		printf("waiting on sample mutex\n");
		// sleep for n ms
		$mutex->sleep(500);
		if($count++ > 60) {
			// reset counter and notify
			$count = 0;
			printf("sample still waiting\n");
			// exit();
		}
	}
	if(!$mutex->getLock()) {
		printf("mutex process failed to lock!\n");
		exit();
	} else {
		printf("mutex process locks\n");
	}

	// do many things

	// release the mutex, now damnit!
	$mutex->releaseLock();

 */

class Mutex {

    var $writeablePath = '';
    var $lockName = '';
    var $fileHandle = null;

    public function __construct($lockName, $writeablePath = null){
        $this->lockName = preg_replace('/[^a-z0-9\-]/', '', $lockName);
        if($writeablePath == null){
            $this->writeablePath = sys_get_temp_dir();
        } else {
            $this->writeablePath = $writeablePath;
        }
    }

    public function getLock(){
        return flock($this->getFileHandle(), LOCK_EX);
    }

    public function getFileHandle(){
        if($this->fileHandle == null){
            $this->fileHandle = fopen($this->getLockFilePath(), 'c');
        }
        return $this->fileHandle;
    }

    public function releaseLock(){
        $success = flock($this->getFileHandle(), LOCK_UN);
        fclose($this->getFileHandle());
        return $success;
    }

    public function getLockFilePath(){
        return $this->writeablePath . DIRECTORY_SEPARATOR . $this->lockName;
    }

    public function isLocked(){
        $fileHandle = fopen($this->getLockFilePath(), 'c');
        $canLock = flock($fileHandle, LOCK_EX | LOCK_NB);
        if($canLock){
            flock($fileHandle, LOCK_UN);
            fclose($fileHandle);
            return false;
        } else {
            fclose($fileHandle);
            return true;
        }
    }

    public function sleep($msSleep) {
    	// 1000 = 1 ms
    	usleep($msSleep * 1000);
    }
}
