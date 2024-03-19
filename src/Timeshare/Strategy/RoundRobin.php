<?php
namespace plibv4\process;
class RoundRobin implements Strategy {
	private array $tasks = [];
	private int $count = 0;
	private ?int $pointer = null;
	function __construct() {
		
	}
	
	function getCount(): int {
		return $this->count;
	}
	
	public function add(TaskEnvelope $timeshared) {
		$this->tasks[$this->count] = $timeshared;
		if($this->count == 0) {
			$this->pointer = 0;
		}
		$this->count++;
	}

	public function getCurrent(): TaskEnvelope {
		return $this->tasks[$this->pointer];
	}

	public function getCurrentIncrement(): TaskEnvelope {
		$current = $this->getCurrent();
		$this->increment();
	return $current;
	}

	public function increment(): void {
		if($this->pointer == $this->count - 1) {
			$this->pointer = 0;
		return;
		}
	$this->pointer++;
	}

	public function isEmpty(): bool {
		return $this->count == null;
	}

	private function modifyPointer($key) {
		if($this->pointer>$key) {
			$this->pointer--;
		}
		if($this->pointer == $this->count - 1 ) {
			$this->pointer = 0;
		}
	}
	
	public function remove(TaskEnvelope $timeshared) {
		$new = array();
		foreach($this->tasks as $key => $value) {
			if($value === $timeshared) {
				$this->modifyPointer($key);
				$this->count--;
				continue;
			}
		$new[] = $value;
		}
		$this->tasks = $new;
		if(empty($this->tasks)) {
			$this->pointer = null;
		}
	}

	public function isValid(): bool {
		
	}
}
