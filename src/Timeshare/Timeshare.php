<?php
namespace plibv4\process;
class Timeshare implements Timeshared {
	private array $timeshared = array();
	private int $pointer = 0;
	private int $count = 0;
	private bool $terminated = false;
	private int $timeout = 30*1000000;
	private int $terminatedAt = 0;
	private TimeshareObservers $timeshareObservers;
	const START = 1;
	const LOOP = 2;
	const FINISH = 3;
	const TERMINATE = 4;
	const PAUSE = 5;
	const RESUME = 6;
	const ERROR = 255;
	function __construct() {
		$this->timeshareObservers = new TimeshareObservers();
	}
	
	function addTimeshareObserver(TimeshareObserver $observer): void {
		#if(array_search($observer, $this->timeshareObservers)!==false) {
		#	return;
		#}
		$this->timeshareObservers->addTimeshareObserver($observer);
	}
	
	function setTimeout(int $seconds, int $microseconds) {
		$this->timeout = $seconds*1000000 + $microseconds;
	}
	
	function getTimeout(): int {
		return $this->timeout;
	}
	
	function getProcessCount(): int {
		return $this->count;
	}
	
	function addTimeshared(Timeshared $timeshared): void {
		$this->timeshared[] = new TaskEnvelope($this, $timeshared, $this->timeshareObservers);
		$this->count++;
	}
	
	function __tsFinish(): void {
		foreach($this->timeshared as $value) {
			$value->finish();
		}
		$this->timeshared = array();
	}

	public function __tsStart(): void {
		
	}
	
	private function callFinish(Timeshared $timeshared): void {
		try {
			$timeshared->__tsFinish();
		} catch (\Exception $e) {
			$this->timeshareObservers->onError($this, $timeshared, $e, Timeshare::FINISH);
			$timeshared->__tsError($e, Timeshare::FINISH);
		return;
		}
	}
	
	public function remove(Timeshared $timeshared, int $status): void {
		$new = array();
		$i = 0;
		/*
		 * We do not remove an item using array_search/unset, but by rebuilding
		 * a new array, so the keys are contiguous again.
		 * The performance penalty of this approach should be negligible.
		 */
		foreach($this->timeshared as $key => $value) {
			if($value->getTimeshared()==$timeshared) {
				$this->pointer = -1;
				if($status === Timeshare::FINISH) {
					$this->callFinish($value->getTimeshared());
				}
				continue;
			}
			$new[] = $value;
			if($this->pointer<0) {
				$this->pointer = $i;
			}
			$i++;
		}
		if($this->pointer<0) {
			$this->pointer = 0;
		}
		$this->timeshared = $new;
		$this->count = count($this->timeshared);
		$this->timeshareObservers->onRemove($this, $timeshared, $status);
	}

	private function callLoop(): void {
		$task = $this->timeshared[$this->pointer];
		if($task->__tsLoop()) {
			$this->pointer++;
		} else {
			$this->remove($task->getTimeshared(), Timeshare::FINISH);
		}
		if($this->pointer==$this->count) {
			$this->pointer = 0;
		}
	}
	
	public function __tsLoop(): bool {
		if(empty($this->timeshared)) {
			return false;
		}
		/**
		 * Implementation of timeout: run kill, then end.
		 */
		if($this->terminated && microtime(true)*1000000 - $this->terminatedAt >= $this->timeout ) {
			$this->__tsKill();
		return false;
		}
		/**
		 * calling __tsLoop as such.
		 */
		$this->callLoop();
		/*
		 *  When Timeshare was terminated, try to terminate all processes on
		 *  every loop.
		 */
		if($this->terminated) {
			if($this->__tsTerminate()) {
				return false;
			}
		}
	return true;
	}

	public function __tsKill(): void {
		foreach($this->timeshared as $value) {
			$value->__tsKill();
		}
		$this->timeshared = array();
	}

	public function __tsPause(): void {
		
	}

	public function __tsResume(): void {
		
	}

	public function __tsTerminate(): bool {
		if(!$this->terminated) {
			$this->terminatedAt = microtime(true)*1000000;
		}
		$this->terminated = true;
		foreach($this->timeshared as $value) {
			if($value->__tsTerminate()) {
				$this->remove($value->getTimeshared(), Timeshare::TERMINATE);
			}
		}
	return empty($this->timeshared);
	}
	
	public function run(): void {
		while($this->__tsLoop()) {
			
		}
	return;
	}
	
	public function __tsError(\Exception $e, int $step): void {
		throw $e;
	}
}