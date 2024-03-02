<?php
namespace plibv4\process;
class Timeshare implements Timeshared {
	private array $timeshared = array();
	private int $pointer = 0;
	private int $count = 0;
	private array $startStack = array();
	private bool $terminated = false;
	private int $timeout = 30*1000000;
	private int $terminatedAt = 0;
	private array $timeshareObservers = array();
	function __construct() {
	}
	
	function addTimeshareObserver(TimeshareObserver $observer): void {
		if(array_search($observer, $this->timeshareObservers)!==false) {
			return;
		}
		$this->timeshareObservers[] = $observer;
	}
	
	function setTimeout(int $seconds, int $microseconds) {
		$this->timeout = $seconds*1000000 + $microseconds;
	}
	
	function getProcessCount() {
		return $this->count;
	}
	
	function addTimeshared(Timeshared $timeshared) {
		$this->timeshared[] = $timeshared;
		$this->count = count($this->timeshared);
		$this->startStack[] = $timeshared;
		foreach($this->timeshareObservers as $value) {
			$value->onAdd($this, $timeshared);
		}
	}
	
	function finish(): void {
		foreach($this->timeshared as $value) {
			$value->finish();
		}
		$this->timeshared = array();
	}

	public function start(): void {
		
	}
	
	private function remove(Timeshared $timeshared, int $status) {
		$new = array();
		$i = 0;
		foreach($this->timeshared as $key => $value) {
			if($value==$timeshared) {
				$this->pointer = -1;
				if($status === TimeshareObserver::FINISHED) {
					$value->finish();
				}
				unset($this->startStack[$key]);
				continue;
			}
			$new[] = $value;
			if($this->pointer<0) {
				$this->pointer = $i;
			}
			$i++;
		}
		if($this->pointer<0) {
			$this->pointer = 0;
		}
		$this->timeshared = $new;
		$this->count = count($this->timeshared);
		foreach($this->timeshareObservers as $value) {
			$value->onRemove($this, $timeshared, $status);
		}
	}
	
	public function loop(): bool {
		if(empty($this->timeshared)) {
			return false;
		}
		if($this->terminated && microtime(true)*1000000 - $this->terminatedAt >= $this->timeout ) {
			$this->kill();
		return false;
		}
		/**
		 * I don't like to have this in every loop, but for now I see no
		 * better solution.
		 */
		if(isset($this->startStack[$this->pointer])) {
			$this->startStack[$this->pointer]->start();
			foreach($this->timeshareObservers as $value) {
				$value->onStart($this, $this->startStack[$this->pointer]);
			}
			unset($this->startStack[$this->pointer]);
		}
		if($this->timeshared[$this->pointer]->loop()) {
			$this->pointer++;
		} else {
			$this->remove($this->timeshared[$this->pointer], TimeshareObserver::FINISHED);
		}
		if($this->pointer==$this->count) {
			$this->pointer = 0;
		}
		/*
		 *  When Timeshare was terminated, try to terminate all processes on
		 *  every loop.
		 */
		if($this->terminated) {
			if($this->terminate()) {
				return false;
			}
		}
	return true;
	}

	public function kill(): void {
		foreach($this->timeshared as $value) {
			$value->kill();
		}
		$this->timeshared = array();
	}

	public function pause(): void {
		
	}

	public function resume(): void {
		
	}

	public function terminate(): bool {
		if(!$this->terminated) {
			$this->terminatedAt = microtime(true)*1000000;
		}
		$this->terminated = true;
		foreach($this->timeshared as $value) {
			if($value->terminate()) {
				$this->remove($value, TimeshareObserver::TERMINATED);
			}
		}
	return empty($this->timeshared);
	}
	
	public function run(): void {
		while($this->loop()) {
			
		}
	return;
	}
}