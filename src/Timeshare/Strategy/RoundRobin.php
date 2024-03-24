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
	
	public function add(TaskEnvelope $Task) {
		$this->tasks[$this->count] = $Task;
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
	
	public function remove(TaskEnvelope $Task) {
		$new = array();
		foreach($this->tasks as $key => $value) {
			if($value === $Task) {
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
	
	public function getItem(int $item): TaskEnvelope {
		return $this->tasks[$item];
	}
	
	public function getItemByTask(Task $Task): TaskEnvelope {
		for($i = 0; $i < $this->getCount(); $i++) {
			if($this->getItem($i)->getTask() === $Task) {
				return $this->getItem($i);
			}
		}
	throw new \RuntimeException("Task '". get_class($Task)."' not found in '". get_class($this)."'");
	}

	public function hasItemByTask(Task $Task): bool {
		for($i = 0; $i < $this->getCount(); $i++) {
			if($this->getItem($i)->getTask() === $Task) {
				return true;
			}
		}
	return false;
	}
}
