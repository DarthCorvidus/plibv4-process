<?php
use plibv4\process\TimeshareObserver;
use plibv4\process\Timeshare;
use plibv4\process\Task;
use PHPUnit\Framework\TestCase;
class TrackObserver implements TimeshareObserver {
	public int $countAdded = 0;
	public int $countRemoved = 0;
	public int $countError = 0;
	public int $countStarted = 0;
	public int $countPaused = 0;
	public int $countResumed = 0;
	public ?Timeshare $lastSchedule = null;
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

	public function onAdd(Timeshare $timeshare, Task $task): void {
		$this->countAdded++;
		$this->lastSchedule = $timeshare;
		$this->lastTaskAdded = $task;
	}

	public function onAddCalled(Timeshare $timeshare, Task $task, int $count) {
		TestCase::assertSame($count, $this->countAdded);
		TestCase::assertSame($timeshare, $this->lastSchedule);
		TestCase::assertSame($task, $this->lastTaskAdded);
	}

	public function onAddNotCalled() {
		TestCase::assertSame(0, $this->countAdded);
		TestCase::assertSame(null, $this->lastTaskAdded);
	}

	public function onError(Timeshare $timeshare, Task $task, \Exception $e, int $step): void {
		$this->countError++;
		$this->lastSchedule = $timeshare;
		$this->lastTaskError = $task;
		$this->lastException = $e;
		$this->lastStepError = $step;
	}
	
	public function onErrorCalled(Timeshare $timeshare, Task $task, \Exception $e, int $step, int $count) {
		TestCase::assertSame($count, $this->countError);
		TestCase::assertSame($timeshare, $this->lastSchedule);
		TestCase::assertSame($task, $this->lastTaskError);
		TestCase::assertSame($e, $this->lastException);
		TestCase::assertSame($step, $this->lastStepError);
	}

	public function onErrorNotCalled() {
		TestCase::assertSame(0, $this->countError);
		TestCase::assertSame(null, $this->lastTaskError);
		TestCase::assertSame(null, $this->lastException);
		TestCase::assertSame(0, $this->lastStepError);
	}

	public function onRemove(Timeshare $timeshare, Task $task, int $step): void {
		$this->countRemoved++;
		$this->lastSchedule = $timeshare;
		$this->lastTaskRemoved = $task;
		$this->lastStepRemoved = $step;
	}

	public function onRemoveCalled(Timeshare $timeshare, Task $task, int $step, int $count): void {
		TestCase::assertSame($count, $this->countRemoved);
		TestCase::assertSame($timeshare, $this->lastSchedule);
		TestCase::assertSame($task, $this->lastTaskRemoved);
		TestCase::assertSame($step, $this->lastStepRemoved);
	}

	public function onRemoveNotCalled(): void {
		TestCase::assertSame(0, $this->countRemoved);
		TestCase::assertSame(null, $this->lastTaskRemoved);
		TestCase::assertSame(0, $this->lastStepRemoved);
	}

	public function onStart(Timeshare $timeshare, Task $task): void {
		$this->countStarted++;
		$this->lastSchedule = $timeshare;
		$this->lastTaskStarted = $task;
	}

	public function onStartCalled(Timeshare $timeshare, Task $task, int $count) {
		TestCase::assertSame($count, $this->countStarted);
		TestCase::assertSame($timeshare, $this->lastSchedule);
		TestCase::assertSame($task, $this->lastTaskStarted);
	}

	public function onStartNotCalled(): void {
		TestCase::assertSame(0, $this->countStarted);
		TestCase::assertSame(null, $this->lastTaskStarted);
	}
	
	public function onPause(Timeshare $timeshare, Task $task): void {
		$this->countPaused++;
		$this->lastSchedule = $timeshare;
		$this->lastTaskPaused = $task;
	}

	public function onPauseCalled(Timeshare $timeshare, Task $task, int $count) {
		TestCase::assertSame($count, $this->countPaused);
		TestCase::assertSame($timeshare, $this->lastSchedule);
		TestCase::assertSame($task, $this->lastTaskPaused);
	}

	public function onPauseNotCalled() {
		TestCase::assertSame(0, $this->countPaused);
		TestCase::assertSame(null, $this->lastTaskPaused);
	}
	
	public function onResume(Timeshare $timeshare, Task $task): void {
		$this->countResumed++;
		$this->lastSchedule = $timeshare;
		$this->lastTaskResumed = $task;
	}

	public function onResumeCalled(Timeshare $timeshare, Task $task, int $count) {
		TestCase::assertSame($count, $this->countResumed);
		TestCase::assertSame($timeshare, $this->lastSchedule);
		TestCase::assertSame($task, $this->lastTaskResumed);
	}

	public function onResumeNotCalled() {
		TestCase::assertSame(0, $this->countResumed);
		TestCase::assertSame(null, $this->lastTaskResumed);
	}

	
}
