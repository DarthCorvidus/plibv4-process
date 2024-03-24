<?php
namespace plibv4\process;
class Timeshare implements Task, Scheduler {
	private Strategy $strategy;
	private int $timeout = 30*1000000;
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
	
	function setTimeout(int $seconds, int $microseconds): void {
		$this->timeout = $seconds*1000000 + $microseconds;
	}
	
	function getTimeout(): int {
		return $this->timeout;
	}
	
	function getTaskCount(): int {
		return $this->strategy->getCount();
	}
	
	function addTask(Task $Task): void {
		$this->strategy->add(new TaskEnvelope($this, $Task, $this->timeshareObservers));
	}
	
	function __tsFinish(): void {
		
	}

	public function __tsStart(): void {
		
	}
	
	private function callFinish(Task $Task): void {
		try {
			$Task->__tsFinish();
		} catch (\Exception $e) {
			$this->timeshareObservers->onError($this, $Task, $e, Timeshare::FINISH);
			$Task->__tsError($e, Timeshare::FINISH);
		return;
		}
	}
	
	private function remove(TaskEnvelope $taskEnvelope, int $status): void {
		$this->strategy->remove($taskEnvelope);
		if($status === Timeshare::FINISH) {
			$this->callFinish($taskEnvelope->getTask());
		}
		$this->timeshareObservers->onRemove($this, $taskEnvelope->getTask(), $status);
	return;
	}

	public function __tsLoop(): bool {
		if($this->strategy->getCount() === 0) {
			return false;
		}
		$task = $this->strategy->getCurrentIncrement();
		if(!$task->loop()) {
			$this->remove($task, $task->getState());
		}
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
	
	public function hasTask(Task $Task): bool {
		return $this->strategy->hasItemByTask($Task);
	}
	
	private function getTaskEnvelope(Task $Task): TaskEnvelope {
		try {
			$taskEnvelope = $this->strategy->getItemByTask($Task);
			return $taskEnvelope;
		} catch (\Exception $ex) {
			throw new \RuntimeException("Task '". get_class($Task)."' not found in Scheduler '". get_class($this)."'");
		}
	}
	
	public function terminate(Task $Task): void {
		$this->getTaskEnvelope($Task)->terminate();
	}
	
	public function kill(Task $Task): void {
		$this->getTaskEnvelope($Task)->kill();
	}
	
	public function pause(Task $Task): void {
		$this->getTaskEnvelope($Task)->pause();
	}
	
	public function resume(Task $Task): void {
		$this->getTaskEnvelope($Task)->resume();
	}
}