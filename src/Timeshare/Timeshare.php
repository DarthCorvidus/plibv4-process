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
	const START = 1;
	const LOOP = 2;
	const FINISH = 3;
	const TERMINATE = 4;
	const PAUSE = 5;
	const RESUME = 6;
	const ERROR = 255;
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
			$task = $this->startStack[$this->pointer];
			try {
				$task->__tsStart();
			} catch (\Exception $ex) {
				# Here be onError observer
				/*
				 * call Timeshared::__tsError in case task has not realized
				 * it is dead.
				 */
				$task->__tsError($ex, Timeshare::START);
				
				$this->remove($task, Timeshare::ERROR);
			return;
			}
			
			foreach($this->timeshareObservers as $value) {
				$value->onStart($this, $this->startStack[$this->pointer]);
			}
			unset($this->startStack[$this->pointer]);
		}
	}
	
	private function callFinish(Timeshared $timeshared) {
		try {
			$timeshared->__tsFinish();
		} catch (\Exception $e) {
			$timeshared->__tsError($e, Timeshare::FINISH);
		return;
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
				if($status === Timeshare::FINISH) {
					$this->callFinish($value);
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

	private function callLoop() {
		$task = $this->timeshared[$this->pointer];
		try {
			if($task->__tsLoop()) {
				$this->pointer++;
			} else {
				$this->remove($task, Timeshare::FINISH);
			}
		} catch (\Exception $e) {
			$task->__tsError($e, Timeshare::LOOP);
			$this->remove($task, Timeshare::ERROR);
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
		 * I don't like to have this in every loop, but for now I see no
		 * better solution.
		 */
		$this->callStart();
		/**
		 * CallStart can lead to an empty schedule, if the only task died on
		 * __tsStart().
		 */
		if(empty($this->timeshared)) {
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
				$this->remove($value, Timeshare::TERMINATE);
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