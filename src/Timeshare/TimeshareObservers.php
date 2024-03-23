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

	function onAdd(Timeshare $timeshare, Timeshared $timeshared): void {
		foreach($this->timeshareObservers as $value) {
			$value->onAdd($timeshare, $timeshared);
		}
	}
	function onStart(Timeshare $timeshare, Timeshared $timeshared): void {
		foreach($this->timeshareObservers as $value) {
			$value->onStart($timeshare, $timeshared);
		}
	}
	
	function onPause(Timeshare $timeshare, Timeshared $timeshared): void {
		foreach($this->timeshareObservers as $value) {
			$value->onPause($timeshare, $timeshared);
		}
	}

	function onResume(Timeshare $timeshare, Timeshared $timeshared): void {
		foreach($this->timeshareObservers as $value) {
			$value->onResume($timeshare, $timeshared);
		}
	}

	function onRemove(Timeshare $timeshare, Timeshared $timeshared, int $step): void {
		foreach($this->timeshareObservers as $value) {
			$value->onRemove($timeshare, $timeshared, $step);
		}
	}
	
	function onError(Timeshare $timeshare, Timeshared $timeshared, \Exception $e, int $step): void {
		foreach($this->timeshareObservers as $value) {
			$value->onError($timeshare, $timeshared, $e, $step);
		}
	}
}
