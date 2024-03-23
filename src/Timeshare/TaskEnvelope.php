<?php
namespace plibv4\process;
class TaskEnvelope {
	private Timeshared $task;
	private Timeshare $scheduler;
	private TimeshareObservers $taskObservers;
	private bool $started = false;
	private ?int $terminatedAt = null;
	private bool $paused = false;
	private ?int $state = null;
	function __construct(Timeshare $scheduler, Timeshared $task, TimeshareObservers $observers) {
		$this->task = $task;
		$this->taskObservers = $observers;
		$this->scheduler = $scheduler;
		$this->taskObservers->onAdd($this->scheduler, $this->task);
	}
	
	function getState(): int {
		return $this->state;
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
			$this->state = Timeshare::ERROR;
			#$this->scheduler->remove($this->task, Timeshare::ERROR);
		}
	}
	
	private function runLoop(): bool {
		if($this->paused) {
			return true;
		}
		try {
			$result = $this->task->__tsLoop();
			if($result == false) {
				$this->state = Timeshare::FINISH;
				#$this->scheduler->remove($this->task, Timeshare::FINISH);
			}
			return $result;
		} catch (\Exception $e) {
			$this->task->__tsError($e, Timeshare::LOOP);
			$this->taskObservers->onError($this->scheduler, $this->task, $e, Timeshare::LOOP);
			$this->state = Timeshare::ERROR;
			#$this->scheduler->remove($this->task, Timeshare::ERROR);
		return false;
		}
	}
	
	private function runTerminate(): bool {
		if(microtime(true)*1000000 - $this->terminatedAt >= $this->scheduler->getTimeout()) {
			$this->kill();
			$this->state = Timeshare::KILL;
			#$this->scheduler->remove($this->task, Timeshare::KILL);
		return true;
		}
		if($this->task->__tsTerminate()) {
			$this->state = Timeshare::TERMINATE;
			#this->scheduler->remove($this->task, Timeshare::TERMINATE);
		return true;
		}
	return false;
	}
	
	function loop(): bool {
		if($this->state!==null) {
			return false;
		}
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
		$this->state = Timeshare::KILL;
		#$this->scheduler->remove($this->task, Timeshare::KILL);
	}

	public function pause(): void {
		try {
			$this->task->__tsPause();
			$this->taskObservers->onPause($this->scheduler, $this->task);
			$this->paused = true;
		} catch (\Exception $e) {
			$this->task->__tsError($e, Timeshare::PAUSE);
			$this->state = Timeshare::PAUSE;
			$this->taskObservers->onError($this->scheduler, $this->task, $e, Timeshare::PAUSE);
		}
	}

	public function resume(): void {
		try {
			$this->task->__tsResume();
			$this->taskObservers->onResume($this->scheduler, $this->task);
			$this->paused = false;
		} catch (\Exception $e) {
			$this->task->__tsError($e, Timeshare::RESUME);
			$this->state = Timeshare::RESUME;
			$this->taskObservers->onError($this->scheduler, $this->task, $e, Timeshare::RESUME);
		}
	}

	public function terminate(): bool {
		if($this->terminatedAt == null) {
			$this->terminatedAt = microtime(true)*1000000;
		}
	return false;
	}
}
