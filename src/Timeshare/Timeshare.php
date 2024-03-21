<?php
namespace plibv4\process;
class Timeshare implements Timeshared {
	private Strategy $strategy;
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
		$this->strategy = new RoundRobin();
	}
	
	function addTimeshareObserver(TimeshareObserver $observer): void {
		$this->timeshareObservers->addTimeshareObserver($observer);
	}
	
	function setTimeout(int $seconds, int $microseconds) {
		$this->timeout = $seconds*1000000 + $microseconds;
	}
	
	function getTimeout(): int {
		return $this->timeout;
	}
	
	function getProcessCount(): int {
		return $this->strategy->getCount();
	}
	
	function addTimeshared(Timeshared $timeshared): void {
		$this->strategy->add(new TaskEnvelope($this, $timeshared, $this->timeshareObservers));
	}
	
	function __tsFinish(): void {
		
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
	
	private function remove(TaskEnvelope $taskEnvelope, int $status): void {
		$this->strategy->remove($taskEnvelope);
		if($status === Timeshare::FINISH) {
			$this->callFinish($taskEnvelope->getTimeshared());
		}
		$this->timeshareObservers->onRemove($this, $taskEnvelope->getTimeshared(), $status);
	return;
	}

	private function callLoop(): void {
		$task = $this->strategy->getCurrentIncrement();
		if(!$task->loop()) {
			$this->remove($task, $task->getState());
		}
	}
	
	public function __tsLoop(): bool {
		if($this->strategy->getCount() === 0) {
			return false;
		}
		$this->callLoop();
	return true;
	}

	public function __tsKill(): void {
		for($i = 0; $i < $this->strategy->getCount(); $i++) {
			$this->strategy->getItem($i)->kill();
		}
	}

	public function __tsPause(): void {
		
	}

	public function __tsResume(): void {
		
	}

	public function __tsTerminate(): bool {
		if($this->strategy->getCount()===0) {
			return true;
		}
		for($i = 0; $i < $this->strategy->getCount(); $i++) {
			$this->strategy->getItem($i)->terminate();
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
		for($i = 0; $i < $this->strategy->getCount(); $i++) {
			if($this->strategy->getItem($i)->getTimeshared() === $timeshared) {
				return true;
			}
		}
	return false;
	}
	
	private function getTaskEnvelope(Timeshared $timeshared): TaskEnvelope {
		for($i = 0; $i < $this->strategy->getCount(); $i++) {
			if($this->strategy->getItem($i)->getTimeshared() === $timeshared) {
				return $this->strategy->getItem($i);
			}
		}
	throw new \RuntimeException("Task '". get_class($timeshared)."' not found in Scheduler '". get_class($this)."'");
	}
	
	public function terminate(Timeshared $timeshared): void {
		$this->getTaskEnvelope($timeshared)->terminate();
	}
	
	public function kill(Timeshared $timeshared): void {
		$this->getTaskEnvelope($timeshared)->kill();
	}
	
	public function pause(Timeshared $timeshared): void {
		$this->getTaskEnvelope($timeshared)->pause();
	}
	
	public function resume(Timeshared $timeshared): void {
		$this->getTaskEnvelope($timeshared)->resume();
	}

}