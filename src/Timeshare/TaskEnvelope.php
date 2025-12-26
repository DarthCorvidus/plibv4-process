<?php
namespace plibv4\process;
class TaskEnvelope {
	private Task $task;
	private Scheduler $scheduler;
	private TimeshareObservers $taskObservers;
	private bool $started = false;
	private ?int $terminatedAt = null;
	private bool $paused = false;
	private ?int $state = null;
	function __construct(Scheduler $scheduler, Task $task, TimeshareObservers $observers) {
		$this->task = $task;
		$this->taskObservers = $observers;
		$this->scheduler = $scheduler;
		$this->taskObservers->onAdd($this->scheduler, $this->task);
	}
	
	function getState(): int {
		if($this->state === null) {
			throw new \RuntimeException("Unable to determine state");
		}
		return $this->state;
	}
	
	function getTask(): Task {
		return $this->task;
	}
	
	private function runStart(): void {
		try {
			$this->task->__tsStart($this->scheduler);
			$this->taskObservers->onStart($this->scheduler, $this->task);
			$this->started = true;
		} catch (\Exception $e) {
			$this->task->__tsError($this->scheduler, $e, Scheduler::START);
			$this->taskObservers->onError($this->scheduler, $this->task, $e, Scheduler::START);
			$this->state = Scheduler::ERROR;
			#$this->scheduler->remove($this->task, Scheduler::ERROR);
		}
	}
	
	private function runLoop(): bool {
		if($this->paused) {
			return true;
		}
		try {
			$result = $this->task->__tsLoop($this->scheduler);
			if($result == false) {
				$this->state = Scheduler::FINISH;
				#$this->scheduler->remove($this->task, Scheduler::FINISH);
			}
			return $result;
		} catch (\Exception $e) {
			$this->task->__tsError($this->scheduler, $e, Scheduler::LOOP);
			$this->taskObservers->onError($this->scheduler, $this->task, $e, Scheduler::LOOP);
			$this->state = Scheduler::ERROR;
			#$this->scheduler->remove($this->task, Scheduler::ERROR);
		return false;
		}
	}
	
	private function runTerminate(): bool {
		if($this->terminatedAt===null) {
			// should not happen.
			throw new \RuntimeException("invalid null value");
		}
		/**
		 * @psalm-suppress InvalidOperand
		 */
		if(microtime(true)*1000000 - $this->terminatedAt >= $this->scheduler->getTimeout()) {
			$this->kill();
			$this->state = Scheduler::KILL;
			#$this->scheduler->remove($this->task, Scheduler::KILL);
		return true;
		}
		if($this->task->__tsTerminate($this->scheduler)) {
			$this->state = Scheduler::TERMINATE;
			#this->scheduler->remove($this->task, Scheduler::TERMINATE);
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
		$this->task->__tsKill($this->scheduler);
		$this->state = Scheduler::KILL;
		#$this->scheduler->remove($this->task, Scheduler::KILL);
	}

	public function pause(): void {
		try {
			$this->task->__tsPause($this->scheduler);
			$this->taskObservers->onPause($this->scheduler, $this->task);
			$this->paused = true;
		} catch (\Exception $e) {
			$this->task->__tsError($this->scheduler, $e, Scheduler::PAUSE);
			$this->state = Scheduler::PAUSE;
			$this->taskObservers->onError($this->scheduler, $this->task, $e, Scheduler::PAUSE);
		}
	}

	public function resume(): void {
		try {
			$this->task->__tsResume($this->scheduler);
			$this->taskObservers->onResume($this->scheduler, $this->task);
			$this->paused = false;
		} catch (\Exception $e) {
			$this->task->__tsError($this->scheduler, $e, Scheduler::RESUME);
			$this->state = Scheduler::RESUME;
			$this->taskObservers->onError($this->scheduler, $this->task, $e, Scheduler::RESUME);
		}
	}

	public function terminate(): bool {
		if($this->terminatedAt == null) {
			/**
			 * @psalm-suppress InvalidOperand
			 */
			$this->terminatedAt = (int)(microtime(true)*1000000);
		}
	return false;
	}
}
