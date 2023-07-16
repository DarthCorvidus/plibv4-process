<?php
class ProcessPool {
	private $pool = array();
	function __construct() {
		;
	}
	
	function addProcess(string $id, Process $process) {
		$this->pool[$id] = $process;
	}
	
	function hasProcess(string $id) {
		return isset($this->pool[$id]);
	}
	
	function getProcess(string $id): Process {
		if(!$this->hasProcess($id)) {
			throw new OutOfBoundsException("process ".$id." not in process pool");
		}
	return $this->pool[$id];
	}
	
	function removeProcess(string $id) {
		if(!$this->hasProcess($id)) {
			throw new OutOfBoundsException("process ".$id." not in process pool");
		}
		unset($this->pool[$id]);
	}
}
