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
	public int $lastStep = 0;
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
		TestCase::assertSame($this->countAdded, $count);
		TestCase::assertSame($timeshare, $this->lastSchedule);
		TestCase::assertSame($timeshared, $this->lastTaskAdded);
	}

	public function onAddCalledOnly(Timeshare $timeshare, Timeshared $timeshared, int $count) {
		TestCase::assertSame($timeshared, $this->lastSchedule);
		TestCase::assertSame($count, $this->countAdded);
		TestCase::assertSame(0, $this->countRemoved);
		TestCase::assertSame(0, $this->countError);
		TestCase::assertSame(0, $this->countStarted);
		TestCase::assertSame(0, $this->countPaused);
		TestCase::assertSame(0, $this->countResumed);
		TestCase::assertSame(0, $this->lastStep);
		TestCase::assertSame(null, $this->lastTaskError);
		TestCase::assertSame(null, $this->lastTaskRemoved);
		TestCase::assertSame($timeshare, $this->lastTaskAdded);
		TestCase::assertSame(null, $this->lastTaskStarted);
		TestCase::assertSame(null, $this->lastTaskPaused);
		TestCase::assertSame(null, $this->lastTaskResumed);
		TestCase::assertSame(null, $this->lastException);
	}
	
	public function onError(Timeshare $timeshare, Timeshared $timeshared, \Exception $e, int $step): void {
		$this->countError++;
		$this->lastSchedule = $timeshare;
		$this->lastTaskError = $timeshared;
		$this->lastException = $e;
		$this->lastStep = $step;
	}

	public function onRemove(Timeshare $timeshare, Timeshared $timeshared, int $step): void {
		$this->countRemoved++;
		$this->lastSchedule = $timeshare;
		$this->lastTaskRemoved = $timeshared;
		$this->lastStep = $step;
	}

	public function onStart(Timeshare $timeshare, Timeshared $timeshared): void {
		$this->countStarted++;
		$this->lastSchedule = $timeshare;
		$this->lastTaskStarted = $timeshared;
	}
	
	public function onPause(Timeshare $timeshare, Timeshared $timeshared): void {
		$this->countPaused++;
		$this->lastSchedule = $timeshare;
		$this->lastTaskPaused = $timeshared;
	}
	
	public function onResume(Timeshare $timeshare, Timeshared $timeshared): void {
		$this->countResumed++;
		$this->lastSchedule = $timeshare;
		$this->lastTaskResumed = $timeshared;
	}
	
}
