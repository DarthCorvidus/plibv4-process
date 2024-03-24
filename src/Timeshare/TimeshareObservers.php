<?php
namespace plibv4\process;
class TimeshareObservers {
	private array $timeshareObservers = array();
	function addTimeshareObserver(TimeshareObserver $observer): void {
		if(array_search($observer, $this->timeshareObservers)!==false) {
			return;
		}
		$this->timeshareObservers[] = $observer;
	}

	function onAdd(Timeshare $timeshare, Task $Task): void {
		foreach($this->timeshareObservers as $value) {
			$value->onAdd($timeshare, $Task);
		}
	}
	function onStart(Timeshare $timeshare, Task $Task): void {
		foreach($this->timeshareObservers as $value) {
			$value->onStart($timeshare, $Task);
		}
	}
	
	function onPause(Timeshare $timeshare, Task $Task): void {
		foreach($this->timeshareObservers as $value) {
			$value->onPause($timeshare, $Task);
		}
	}

	function onResume(Timeshare $timeshare, Task $Task): void {
		foreach($this->timeshareObservers as $value) {
			$value->onResume($timeshare, $Task);
		}
	}

	function onRemove(Timeshare $timeshare, Task $Task, int $step): void {
		foreach($this->timeshareObservers as $value) {
			$value->onRemove($timeshare, $Task, $step);
		}
	}
	
	function onError(Timeshare $timeshare, Task $Task, \Exception $e, int $step): void {
		foreach($this->timeshareObservers as $value) {
			$value->onError($timeshare, $Task, $e, $step);
		}
	}
}
