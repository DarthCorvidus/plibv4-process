<?php
namespace plibv4\process;
class Timeshare implements Timeshared {
	private array $timeshared = array();
	private int $pointer = 0;
	private int $allCount = 0;
	private int $activeCount = 0;
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
	
	function getProcessCount(): int {
		return $this->allCount;
	}
	
	function addTimeshared(Timeshared $timeshared): void {
		#$this->timeshared[$this->count] = $timeshared;
		$this->startStack[] = $timeshared;
		$this->allCount++;
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
	
	private function callStart(): bool {
		if(empty($this->startStack)) {
			return false;
		}
		$task = array_shift($this->startStack);
		try {
			$task->__tsStart();
			$this->timeshared[$this->activeCount] = $task;
			$this->activeCount++;
		} catch (\Exception $ex) {
			# Here be onError observer
			foreach($this->timeshareObservers as $value) {
				$value->onError($this, $task, $ex, self::START);
			}
			/*
			 * call Timeshared::__tsError in case task has not realized
			 * it is dead.
			 */
			$task->__tsError($ex, Timeshare::START);

			#$this->remove($task, Timeshare::ERROR);
		return true;
		}

		foreach($this->timeshareObservers as $value) {
			$value->onStart($this, $task);
		}
	return true;
	}
	
	private function callFinish(Timeshared $timeshared): void {
		try {
			$timeshared->__tsFinish();
		} catch (\Exception $e) {
			foreach ($this->timeshareObservers as $value) {
				$value->onError($this, $timeshared, $e, Timeshare::FINISH);
			}
			$timeshared->__tsError($e, Timeshare::FINISH);
		return;
		}
	}
	
	private function remove(Timeshared $timeshared, int $status): void {
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
		$this->activeCount = count($this->timeshared);
		foreach($this->timeshareObservers as $value) {
			$value->onRemove($this, $timeshared, $status);
		}
	}

	private function callLoop(): void {
		$task = $this->timeshared[$this->pointer];
		try {
			if($task->__tsLoop()) {
				$this->pointer++;
			} else {
				$this->remove($task, Timeshare::FINISH);
			}
		} catch (\Exception $e) {
			$task->__tsError($e, Timeshare::LOOP);
			foreach($this->timeshareObservers as $value) {
				$value->onError($this, $task, $e, Timeshare::LOOP);
			}
			$this->remove($task, Timeshare::ERROR);
		}
		if($this->pointer==$this->activeCount) {
			$this->pointer = 0;
		}
	}
	
	public function __tsLoop(): bool {
		if(empty($this->timeshared) && empty($this->startStack)) {
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
		if($this->callStart()) {
			return true;
		}
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