<?php
namespace plibv4\process;
class TaskEnvelope {
	private Timeshared $task;
	private Timeshare $scheduler;
	private TimeshareObservers $taskObservers;
	private bool $started = false;
	private ?int $terminatedAt = null;
	private bool $paused = false;
	function __construct(Timeshare $scheduler, Timeshared $task, TimeshareObservers $observers) {
		$this->task = $task;
		$this->taskObservers = $observers;
		$this->scheduler = $scheduler;
		$this->taskObservers->onAdd($this->scheduler, $this->task);
	}
	
	function getTimeshared(): Timeshared {
		return $this->task;
	}
	
	private function runStart(): void {
		try {
			$this->task->__tsStart();
			$this->taskObservers->onStart($this->scheduler, $this->task);
			$this->started = true;
		} catch (\Exception $e) {
			$this->task->__tsError($e, Timeshare::START);
			$this->taskObservers->onError($this->scheduler, $this->task, $e, Timeshare::START);
			$this->scheduler->remove($this->task, Timeshare::ERROR);
		}
	}
	
	private function runLoop(): bool {
		if($this->paused) {
			return true;
		}
		try {
			$result = $this->task->__tsLoop();
			if($result == false) {
				$this->scheduler->remove($this->task, Timeshare::FINISH);
			}
			return $result;
		} catch (\Exception $e) {
			$this->task->__tsError($e, Timeshare::LOOP);
			$this->taskObservers->onError($this->scheduler, $this->task, $e, Timeshare::LOOP);
			$this->scheduler->remove($this->task, Timeshare::ERROR);
		return false;
		}
	}
	
	private function runTerminate(): bool {
		if(microtime(true)*1000000 - $this->terminatedAt >= $this->scheduler->getTimeout()) {
			$this->kill();
			$this->scheduler->remove($this->task, Timeshare::KILL);
		return true;
		}
		if($this->task->__tsTerminate()) {
			$this->scheduler->remove($this->task, Timeshare::TERMINATE);
		return true;
		}
	return false;
	}
	
	function loop(): bool {
		if($this->terminatedAt!==null && $this->runTerminate()) {
			return false;
		}
		if(!$this->started) {
			$this->runStart();
		return true;
		}
	return $this->runLoop();
	}

	public function kill(): void {
		$this->task->__tsKill();
		$this->scheduler->remove($this->task, Timeshare::KILL);
	}

	public function pause(): void {
		$this->task->__tsPause();
		$this->paused = true;
	}

	public function resume(): void {
		$this->task->__tsResume();
		$this->paused = false;
	}

	public function terminate(): bool {
		if($this->terminatedAt == null) {
			$this->terminatedAt = microtime(true)*1000000;
		}
	return false;
	}
}
