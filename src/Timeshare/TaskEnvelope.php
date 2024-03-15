<?php
namespace plibv4\process;
class TaskEnvelope {
	private Timeshared $task;
	private Timeshare $scheduler;
	private TimeshareObservers $timesharedObservers;
	private bool $started = false;
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
		try {
			$result = $this->task->__tsLoop();
			return $result;
		} catch (\Exception $e) {
			$this->task->__tsError($e, Timeshare::LOOP);
			$this->taskObservers->onError($this->scheduler, $this->task, $e, Timeshare::LOOP);
			$this->scheduler->remove($this->task, Timeshare::ERROR);
		return false;
		}
	}
	
	function __tsLoop(): bool {
		if(!$this->started) {
			$this->runStart();
		return true;
		}
	return $this->runLoop();
	}

	public function __tsError(\Exception $e, int $step): void {
		$this->task->__tsError($e, $step);
	}

	public function __tsFinish(): void {
		$this->task->__tsFinish();
	}

	public function __tsKill(): void {
		$this->task->__tsKill();
	}

	public function __tsPause(): void {
		$this->task->__tsPause();
	}

	public function __tsResume(): void {
		$this->task->__tsResume();
	}

	public function __tsStart(): void {
		$this->task->__tsStart();
	}

	public function __tsTerminate(): bool {
		return $this->task->__tsTerminate();
	}
}
