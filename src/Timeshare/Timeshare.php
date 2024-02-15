<?php
namespace plibv4\process;
class Timeshare implements Timeshared {
	private array $timeshared = array();
	private int $pointer = 0;
	private int $count = 0;
	private array $startStack = array();
	function __construct() {
		;
	}
	
	function getProcessCount() {
		return $this->count;
	}
	
	function addTimeshared(Timeshared $timeshared) {
		$this->timeshared[] = $timeshared;
		$this->count = count($this->timeshared);
		$this->startStack[] = $timeshared;
	}
	
	function finish(): void {
		foreach($this->timeshared as $value) {
			$value->finish();
		}
		$this->timeshared = array();
	}

	public function start(): void {
		
	}
	
	private function remove(Timeshared $timeshared) {
		$new = array();
		$i = 0;
		foreach($this->timeshared as $key => $value) {
			if($value==$timeshared) {
				$this->pointer = -1;
				$value->finish();
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
	}
	
	public function loop(): bool {
		if(empty($this->timeshared)) {
			return false;
		}
		/**
		 * I don't like to have this in every loop, but for now I see no
		 * better solution.
		 */
		if(isset($this->startStack[$this->pointer])) {
			$this->startStack[$this->pointer]->start();
			unset($this->startStack[$this->pointer]);
		}
		if($this->timeshared[$this->pointer]->loop()) {
			$this->pointer++;
		} else {
			$this->remove($this->timeshared[$this->pointer]);
		}
		if($this->pointer==$this->count) {
			$this->pointer = 0;
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
		foreach($this->timeshared as $value) {
			if($value->terminate()) {
				$this->remove($value);
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