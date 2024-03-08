<?php
namespace plibv4\process;
class Timeshare implements Timeshared {
	private array $timeshared = array();
	private int $pointer = 0;
	private int $count = 0;
	private array $startStack = array();
	private bool $terminated = false;
	private int $timeout = 30*1000000;
	private int $terminatedAt = 0;
	private array $timeshareObservers = array();
	function __construct() {
	}
	
	function addTimeshareObserver(TimeshareObserver $observer): void {
		if(array_search($observer, $this->timeshareObservers)!==false) {
			return;
		}
		$this->timeshareObservers[] = $observer;
	}
	
	function setTimeout(int $seconds, int $microseconds) {
		$this->timeout = $seconds*1000000 + $microseconds;
	}
	
	function getProcessCount() {
		return $this->count;
	}
	
	function addTimeshared(Timeshared $timeshared) {
		$this->timeshared[$this->count] = $timeshared;
		$this->startStack[$this->count] = $timeshared;
		$this->count++;
		foreach($this->timeshareObservers as $value) {
			$value->onAdd($this, $timeshared);
		}
	}
	
	function __tsFinish(): void {
		foreach($this->timeshared as $value) {
			$value->finish();
		}
		$this->timeshared = array();
	}

	public function __tsStart(): void {
		
	}
	
	private function callStart() {
		if(isset($this->startStack[$this->pointer])) {
			$this->startStack[$this->pointer]->__tsStart();
			foreach($this->timeshareObservers as $value) {
				$value->onStart($this, $this->startStack[$this->pointer]);
			}
			unset($this->startStack[$this->pointer]);
		}
	}
	
	private function remove(Timeshared $timeshared, int $status) {
		$new = array();
		$i = 0;
		/*
		 * We do not remove an item using array_search/unset, but by rebuilding
		 * a new array, so the keys are contiguous again.
		 * The performance penalty of this approach should be negligible.
		 */
		foreach($this->timeshared as $key => $value) {
			if($value==$timeshared) {
				$this->pointer = -1;
				if($status === TimeshareObserver::FINISHED) {
					$value->__tsFinish();
				}
				/*
				 * A task might be immediately removed after being added.
				 * Therefore, it gets removed from the stack of tasks that
				 * need to be started.
				 */
				unset($this->startStack[$key]);
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
		foreach($this->timeshareObservers as $value) {
			$value->onRemove($this, $timeshared, $status);
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
		 * I don't like to have this in every loop, but for now I see no
		 * better solution.
		 */
		$this->callStart();
	
		if($this->timeshared[$this->pointer]->__tsLoop()) {
			$this->pointer++;
		} else {
			$this->remove($this->timeshared[$this->pointer], TimeshareObserver::FINISHED);
		}
		if($this->pointer==$this->count) {
			$this->pointer = 0;
		}
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
				$this->remove($value, TimeshareObserver::TERMINATED);
			}
		}
	return empty($this->timeshared);
	}
	
	public function run(): void {
		while($this->__tsLoop()) {
			
		}
	return;
	}
}