<?php
use plibv4\process\TimeshareObserver;
use plibv4\process\Timeshare;
use plibv4\process\Timeshared;
use PHPUnit\Framework\TestCase;
class TrackObserver implements TimeshareObserver {
	public int $countAdded = 0;
	public int $countRemoved = 0;
	public int $countError = 0;
	public int $countStarted = 0;
	public int $countPaused = 0;
	public int $countResumed = 0;
	public ?Timeshare $lastSchedule = null;
	public ?Timeshared $lastTaskError = null;
	public ?Timeshared $lastTaskAdded = null;
	public ?Timeshared $lastTaskRemoved = null;
	public ?Timeshared $lastTaskStarted = null;
	public ?Timeshared $lastTaskPaused = null;
	public ?Timeshared $lastTaskResumed = null;
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

	public function onAdd(Timeshare $timeshare, Timeshared $timeshared): void {
		$this->countAdded++;
		$this->lastSchedule = $timeshare;
		$this->lastTaskAdded = $timeshared;
	}

	public function onAddCalled(Timeshare $timeshare, Timeshared $timeshared, int $count) {
		TestCase::assertSame($count, $this->countAdded);
		TestCase::assertSame($timeshare, $this->lastSchedule);
		TestCase::assertSame($timeshared, $this->lastTaskAdded);
	}

	public function onAddNotCalled() {
		TestCase::assertSame(0, $this->countAdded);
		TestCase::assertSame(null, $this->lastTaskAdded);
	}

	public function onError(Timeshare $timeshare, Timeshared $timeshared, \Exception $e, int $step): void {
		$this->countError++;
		$this->lastSchedule = $timeshare;
		$this->lastTaskError = $timeshared;
		$this->lastException = $e;
		$this->lastStepError = $step;
	}
	
	public function onErrorCalled(Timeshare $timeshare, Timeshared $timeshared, \Exception $e, int $step, int $count) {
		TestCase::assertSame($count, $this->countError);
		TestCase::assertSame($timeshare, $this->lastSchedule);
		TestCase::assertSame($timeshared, $this->lastTaskError);
		TestCase::assertSame($e, $this->lastException);
		TestCase::assertSame($step, $this->lastStepError);
	}

	public function onErrorNotCalled() {
		TestCase::assertSame(0, $this->countError);
		TestCase::assertSame(null, $this->lastTaskError);
		TestCase::assertSame(null, $this->lastException);
		TestCase::assertSame(0, $this->lastStepError);
	}

	public function onRemove(Timeshare $timeshare, Timeshared $timeshared, int $step): void {
		$this->countRemoved++;
		$this->lastSchedule = $timeshare;
		$this->lastTaskRemoved = $timeshared;
		$this->lastStepRemoved = $step;
	}

	public function onRemoveCalled(Timeshare $timeshare, Timeshared $timeshared, int $step, int $count): void {
		TestCase::assertSame($count, $this->countRemoved);
		TestCase::assertSame($timeshare, $this->lastSchedule);
		TestCase::assertSame($timeshared, $this->lastTaskRemoved);
		TestCase::assertSame($step, $this->lastStepRemoved);
	}

	public function onRemoveNotCalled(): void {
		TestCase::assertSame(0, $this->countRemoved);
		TestCase::assertSame(null, $this->lastTaskRemoved);
		TestCase::assertSame(0, $this->lastStepRemoved);
	}

	public function onStart(Timeshare $timeshare, Timeshared $timeshared): void {
		$this->countStarted++;
		$this->lastSchedule = $timeshare;
		$this->lastTaskStarted = $timeshared;
	}

	public function onStartCalled(Timeshare $timeshare, Timeshared $timeshared, int $count) {
		TestCase::assertSame($count, $this->countStarted);
		TestCase::assertSame($timeshare, $this->lastSchedule);
		TestCase::assertSame($timeshared, $this->lastTaskStarted);
	}

	public function onStartNotCalled(): void {
		TestCase::assertSame(0, $this->countStarted);
		TestCase::assertSame(null, $this->lastTaskStarted);
	}
	
	public function onPause(Timeshare $timeshare, Timeshared $timeshared): void {
		$this->countPaused++;
		$this->lastSchedule = $timeshare;
		$this->lastTaskPaused = $timeshared;
	}

	public function onPauseCalled(Timeshare $timeshare, Timeshared $timeshared, int $count) {
		TestCase::assertSame($count, $this->countPaused);
		TestCase::assertSame($timeshare, $this->lastSchedule);
		TestCase::assertSame($timeshared, $this->lastTaskPaused);
	}

	public function onPauseNotCalled() {
		TestCase::assertSame(0, $this->countPaused);
		TestCase::assertSame(null, $this->lastTaskPaused);
	}
	
	public function onResume(Timeshare $timeshare, Timeshared $timeshared): void {
		$this->countResumed++;
		$this->lastSchedule = $timeshare;
		$this->lastTaskResumed = $timeshared;
	}

	public function onResumeCalled(Timeshare $timeshare, Timeshared $timeshared, int $count) {
		TestCase::assertSame($count, $this->countResumed);
		TestCase::assertSame($timeshare, $this->lastSchedule);
		TestCase::assertSame($timeshared, $this->lastTaskResumed);
	}

	public function onResumeNotCalled() {
		TestCase::assertSame(0, $this->countResumed);
		TestCase::assertSame(null, $this->lastTaskResumed);
	}

	
}
