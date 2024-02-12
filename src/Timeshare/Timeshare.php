<?php
namespace plibv4;
namespace plibv4\Timeshare;
class Timeshare implements Timeshared {
	private array $timeshared = array();
	private $pointer = 0;
	private $count = 0;
	function __construct() {
		;
	}
	
	function getProcessCount() {
		return $this->count;
	}
	
	function addTimeshared(Timeshared $timeshared) {
		$this->timeshared[] = $timeshared;
		$this->count = count($this->timeshared);
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