<?php
use plibv4\process\TimeshareObserver;
use plibv4\process\Timeshare;
use plibv4\process\Timeshared;
class TrackObserver implements TimeshareObserver {
	public int $countAdded = 0;
	public int $countRemoved = 0;
	public int $countError = 0;
	public int $countStarted = 0;
	public ?Timeshare $lastSchedule = null;
	public ?Timeshared $lastTaskError = null;
	public ?Timeshared $lastTaskAdded = null;
	public ?Timeshared $lastTaskRemoved = null;
	public ?Timeshared $lastTaskStarted = null;
	public ?\Exception $lastException = null;
	public int $lastStep = 0;
	public function onAdd(Timeshare $timeshare, Timeshared $timeshared): void {
		$this->countAdded++;
		$this->lastSchedule = $timeshare;
		$this->lastTaskAdded = $timeshared;
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
}
