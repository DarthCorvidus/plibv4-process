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
	const KILL = 7;
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
		/*
		 * Increment the pointer only if __tsLoop evaluates to false, as the
		 * task is removed otherwise within TaskEnvelope.
		 */
		if($task->loop()) {
			$this->pointer++;
			if($this->pointer==$this->count) {
				$this->pointer = 0;
			}
		}
	}
	
	public function __tsLoop(): bool {
		if(empty($this->timeshared)) {
			return false;
		}
		$this->callLoop();
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
		foreach($this->timeshared as $value) {
			$value->__tsTerminate();
		}
	return false;
	}
	
	public function run(): void {
		while($this->__tsLoop()) {
			
		}
	return;
	}
	
	public function __tsError(\Exception $e, int $step): void {
		throw $e;
	}
	
	public function hasTimeshared(Timeshared $timeshared): bool {
		foreach($this->timeshared as $value) {
			if($value->getTimeshared() === $timeshared) {
				return true;
			}
		}
	return false;
	}
	
	private function getTaskEnvelope(Timeshared $timeshared): TaskEnvelope {
		foreach($this->timeshared as $value) {
			if($value->getTimeshared() === $timeshared) {
				return $value;
			}
		}
	throw new \RuntimeException("Task '". get_class($timeshared)."' not found in Scheduler '". get_class($this)."'");
	}
	
	public function terminate(Timeshared $timeshared): void {
		$this->getTaskEnvelope($timeshared)->__tsTerminate();
	}
	
	public function kill(Timeshared $timeshared): void {
		$this->getTaskEnvelope($timeshared)->__tsKill();
	}
}