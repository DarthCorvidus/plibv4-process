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

	function onAdd(Timeshare $timeshare, Task $task): void {
		foreach($this->timeshareObservers as $value) {
			$value->onAdd($timeshare, $task);
		}
	}
	function onStart(Timeshare $timeshare, Task $task): void {
		foreach($this->timeshareObservers as $value) {
			$value->onStart($timeshare, $task);
		}
	}
	
	function onPause(Timeshare $timeshare, Task $task): void {
		foreach($this->timeshareObservers as $value) {
			$value->onPause($timeshare, $task);
		}
	}

	function onResume(Timeshare $timeshare, Task $task): void {
		foreach($this->timeshareObservers as $value) {
			$value->onResume($timeshare, $task);
		}
	}

	function onRemove(Timeshare $timeshare, Task $task, int $step): void {
		foreach($this->timeshareObservers as $value) {
			$value->onRemove($timeshare, $task, $step);
		}
	}
	
	function onError(Timeshare $timeshare, Task $task, \Exception $e, int $step): void {
		foreach($this->timeshareObservers as $value) {
			$value->onError($timeshare, $task, $e, $step);
		}
	}
}
