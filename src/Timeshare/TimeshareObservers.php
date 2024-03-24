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

	function onAdd(Scheduler $scheduler, Task $task): void {
		foreach($this->timeshareObservers as $value) {
			$value->onAdd($scheduler, $task);
		}
	}
	function onStart(Scheduler $scheduler, Task $task): void {
		foreach($this->timeshareObservers as $value) {
			$value->onStart($scheduler, $task);
		}
	}
	
	function onPause(Scheduler $scheduler, Task $task): void {
		foreach($this->timeshareObservers as $value) {
			$value->onPause($scheduler, $task);
		}
	}

	function onResume(Scheduler $scheduler, Task $task): void {
		foreach($this->timeshareObservers as $value) {
			$value->onResume($scheduler, $task);
		}
	}

	function onRemove(Scheduler $scheduler, Task $task, int $step): void {
		foreach($this->timeshareObservers as $value) {
			$value->onRemove($scheduler, $task, $step);
		}
	}
	
	function onError(Scheduler $scheduler, Task $task, \Exception $e, int $step): void {
		foreach($this->timeshareObservers as $value) {
			$value->onError($scheduler, $task, $e, $step);
		}
	}
}
