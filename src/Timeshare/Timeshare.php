<?php
namespace plibv4\process;
class Timeshare implements Task, Scheduler {
	private Strategy $strategy;
	private int $timeout = 30*1000000;
	private TimeshareObservers $timeshareObservers;
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
	
	function addTask(Task $task): void {
		$this->strategy->add(new TaskEnvelope($this, $task, $this->timeshareObservers));
	}
	
	function __tsFinish(): void {
		
	}

	public function __tsStart(): void {
		
	}
	
	private function callFinish(Task $task): void {
		try {
			$task->__tsFinish();
		} catch (\Exception $e) {
			$this->timeshareObservers->onError($this, $task, $e, Scheduler::FINISH);
			$task->__tsError($e, Scheduler::FINISH);
		return;
		}
	}
	
	private function remove(TaskEnvelope $taskEnvelope, int $status): void {
		$this->strategy->remove($taskEnvelope);
		if($status === Scheduler::FINISH) {
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
	
	public function hasTask(Task $task): bool {
		return $this->strategy->hasItemByTask($task);
	}
	
	private function getTaskEnvelope(Task $task): TaskEnvelope {
		try {
			$taskEnvelope = $this->strategy->getItemByTask($task);
			return $taskEnvelope;
		} catch (\Exception $ex) {
			throw new \RuntimeException("Task '". get_class($task)."' not found in Scheduler '". get_class($this)."'");
		}
	}
	
	public function terminate(Task $task): void {
		$this->getTaskEnvelope($task)->terminate();
	}
	
	public function kill(Task $task): void {
		$this->getTaskEnvelope($task)->kill();
	}
	
	public function pause(Task $task): void {
		$this->getTaskEnvelope($task)->pause();
	}
	
	public function resume(Task $task): void {
		$this->getTaskEnvelope($task)->resume();
	}
}