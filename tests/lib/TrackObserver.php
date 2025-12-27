<?php
use plibv4\process\TimeshareObserver;
use plibv4\process\Scheduler;
use plibv4\process\Task;
use PHPUnit\Framework\TestCase;
class TrackObserver implements TimeshareObserver {
	public int $countAdded = 0;
	public int $countRemoved = 0;
	public int $countError = 0;
	public int $countStarted = 0;
	public int $countPaused = 0;
	public int $countResumed = 0;
	public ?Scheduler $lastSchedule = null;
	public ?Task $lastTaskError = null;
	public ?Task $lastTaskAdded = null;
	public ?Task $lastTaskRemoved = null;
	public ?Task $lastTaskStarted = null;
	public ?Task $lastTaskPaused = null;
	public ?Task $lastTaskResumed = null;
	public ?\Exception $lastException = null;
	public int $lastStepError = 0;
	public int $lastStepRemoved = 0;
	public function assertSame(mixed $expected, mixed $given): bool {
		if($expected === $given) {
			return true;
		}
		if(is_scalar($expected) && is_scalar($given)) {
			throw new \Exception("expected value ".$expected." does not match actual ".$given." value");
		}
		throw new \Exception("expected value does not match supplied value");
	}

	public function onAdd(Scheduler $scheduler, Task $task): void {
		$this->countAdded++;
		$this->lastSchedule = $scheduler;
		$this->lastTaskAdded = $task;
	}

	public function onAddCalled(Scheduler $scheduler, Task $task, int $count): void {
		TestCase::assertSame($count, $this->countAdded);
		TestCase::assertSame($scheduler, $this->lastSchedule);
		TestCase::assertSame($task, $this->lastTaskAdded);
	}

	public function onAddNotCalled(): void {
		TestCase::assertSame(0, $this->countAdded);
		TestCase::assertSame(null, $this->lastTaskAdded);
	}

	public function onError(Scheduler $scheduler, Task $task, \Exception $e, int $step): void {
		$this->countError++;
		$this->lastSchedule = $scheduler;
		$this->lastTaskError = $task;
		$this->lastException = $e;
		$this->lastStepError = $step;
	}
	
	public function onErrorCalled(Scheduler $scheduler, Task $task, \Exception $e, int $step, int $count): void {
		TestCase::assertSame($count, $this->countError);
		TestCase::assertSame($scheduler, $this->lastSchedule);
		TestCase::assertSame($task, $this->lastTaskError);
		TestCase::assertSame($e, $this->lastException);
		TestCase::assertSame($step, $this->lastStepError);
	}

	public function onErrorNotCalled(): void {
		TestCase::assertSame(0, $this->countError);
		TestCase::assertSame(null, $this->lastTaskError);
		TestCase::assertSame(null, $this->lastException);
		TestCase::assertSame(0, $this->lastStepError);
	}

	public function onRemove(Scheduler $scheduler, Task $task, int $step): void {
		$this->countRemoved++;
		$this->lastSchedule = $scheduler;
		$this->lastTaskRemoved = $task;
		$this->lastStepRemoved = $step;
	}

	public function onRemoveCalled(Scheduler $scheduler, Task $task, int $step, int $count): void {
		TestCase::assertSame($count, $this->countRemoved);
		TestCase::assertSame($scheduler, $this->lastSchedule);
		TestCase::assertSame($task, $this->lastTaskRemoved);
		TestCase::assertSame($step, $this->lastStepRemoved);
	}

	public function onRemoveNotCalled(): void {
		TestCase::assertSame(0, $this->countRemoved);
		TestCase::assertSame(null, $this->lastTaskRemoved);
		TestCase::assertSame(0, $this->lastStepRemoved);
	}

	public function onStart(Scheduler $scheduler, Task $task): void {
		$this->countStarted++;
		$this->lastSchedule = $scheduler;
		$this->lastTaskStarted = $task;
	}

	public function onStartCalled(Scheduler $scheduler, Task $task, int $count): void {
		TestCase::assertSame($count, $this->countStarted);
		TestCase::assertSame($scheduler, $this->lastSchedule);
		TestCase::assertSame($task, $this->lastTaskStarted);
	}

	public function onStartNotCalled(): void {
		TestCase::assertSame(0, $this->countStarted);
		TestCase::assertSame(null, $this->lastTaskStarted);
	}
	
	public function onPause(Scheduler $scheduler, Task $task): void {
		$this->countPaused++;
		$this->lastSchedule = $scheduler;
		$this->lastTaskPaused = $task;
	}

	public function onPauseCalled(Scheduler $scheduler, Task $task, int $count): void {
		TestCase::assertSame($count, $this->countPaused);
		TestCase::assertSame($scheduler, $this->lastSchedule);
		TestCase::assertSame($task, $this->lastTaskPaused);
	}

	public function onPauseNotCalled(): void {
		TestCase::assertSame(0, $this->countPaused);
		TestCase::assertSame(null, $this->lastTaskPaused);
	}
	
	public function onResume(Scheduler $scheduler, Task $task): void {
		$this->countResumed++;
		$this->lastSchedule = $scheduler;
		$this->lastTaskResumed = $task;
	}

	public function onResumeCalled(Scheduler $scheduler, Task $task, int $count): void {
		TestCase::assertSame($count, $this->countResumed);
		TestCase::assertSame($scheduler, $this->lastSchedule);
		TestCase::assertSame($task, $this->lastTaskResumed);
	}

	public function onResumeNotCalled(): void {
		TestCase::assertSame(0, $this->countResumed);
		TestCase::assertSame(null, $this->lastTaskResumed);
	}

	
}
