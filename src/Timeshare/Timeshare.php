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
	
	#[\Override]
	function addTimeshareObserver(TimeshareObserver $observer): void {
		$this->timeshareObservers->addTimeshareObserver($observer);
	}
	
	#[\Override]
	function setTimeout(int $seconds, int $microseconds): void {
		$this->timeout = $seconds*1000000 + $microseconds;
	}
	
	#[\Override]
	function getTimeout(): int {
		return $this->timeout;
	}
	
	#[\Override]
	function getTaskCount(): int {
		return $this->strategy->getCount();
	}
	
	#[\Override]
	function addTask(Task $task): void {
		$this->strategy->add(new TaskEnvelope($this, $task, $this->timeshareObservers));
	}
	
	#[\Override]
	function __tsFinish(Scheduler $sched): void {
		
	}

	#[\Override]
	public function __tsStart(Scheduler $sched): void {
		
	}
	
	private function callFinish(Task $task): void {
		try {
			$task->__tsFinish($this);
		} catch (\Exception $e) {
			$this->timeshareObservers->onError($this, $task, $e, Scheduler::FINISH);
			$task->__tsError($this, $e, Scheduler::FINISH);
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

	#[\Override]
	public function __tsLoop(Scheduler $sched): bool {
		if($this->strategy->getCount() === 0) {
			return false;
		}
		$task = $this->strategy->getCurrentIncrement();
		if(!$task->loop()) {
			$this->remove($task, $task->getState());
		}
	return true;
	}

	#[\Override]
	public function __tsKill(Scheduler $sched): void {
		for($i = 0; $i < $this->strategy->getCount(); $i++) {
			$this->strategy->getItem($i)->kill();
		}
	}

	#[\Override]
	public function __tsPause(Scheduler $sched): void {
		
	}

	#[\Override]
	public function __tsResume(Scheduler $sched): void {
		
	}

	#[\Override]
	public function terminateAll(): void {
		for($i = 0; $i < $this->strategy->getCount(); $i++) {
			$this->strategy->getItem($i)->terminate();
		}
	}
	
	#[\Override]
	public function __tsTerminate(Scheduler $sched): bool {
		if($this->strategy->getCount()===0) {
			return true;
		}
		for($i = 0; $i < $this->strategy->getCount(); $i++) {
			$this->strategy->getItem($i)->terminate();
		}
	return false;
	}
	
	#[\Override]
	public function run(): void {
		while($this->__tsLoop($this)) {
			
		}
	return;
	}
	
	#[\Override]
	public function __tsError(Scheduler $sched, \Exception $e, int $step): void {
		throw $e;
	}
	
	#[\Override]
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
	
	#[\Override]
	public function terminate(Task $task): void {
		$this->getTaskEnvelope($task)->terminate();
	}
	
	#[\Override]
	public function kill(Task $task): void {
		$this->getTaskEnvelope($task)->kill();
	}
	
	#[\Override]
	public function pause(Task $task): void {
		$this->getTaskEnvelope($task)->pause();
	}
	
	#[\Override]
	public function resume(Task $task): void {
		$this->getTaskEnvelope($task)->resume();
	}
}