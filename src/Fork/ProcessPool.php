<?php
class ProcessPool {
	private array $pool = array();
	function __construct() {
		;
	}
	
	function addProcess(string $id, Process $process): void {
		$this->pool[$id] = $process;
	}
	
	function hasProcess(string $id): bool {
		return isset($this->pool[$id]);
	}
	
	function getProcess(string $id): Process {
		if(!$this->hasProcess($id)) {
			throw new OutOfBoundsException("process ".$id." not in process pool");
		}
	return $this->pool[$id];
	}
	
	function removeProcess(string $id): void {
		if(!$this->hasProcess($id)) {
			throw new OutOfBoundsException("process ".$id." not in process pool");
		}
		unset($this->pool[$id]);
	}
}
