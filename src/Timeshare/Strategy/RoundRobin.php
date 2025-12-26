<?php
namespace plibv4\process;
class RoundRobin implements Strategy {
	/** @var list<TaskEnvelope> */
	private array $tasks = [];
	private int $count = 0;
	private ?int $pointer = null;
	function __construct() {
		
	}
	
	#[\Override]
	function getCount(): int {
		return $this->count;
	}
	
	#[\Override]
	public function add(TaskEnvelope $task): void {
		$this->tasks[] = $task;
		if($this->count === 0) {
			$this->pointer = 0;
		}
		$this->count++;
	}

	#[\Override]
	public function getCurrent(): TaskEnvelope {
		if($this->pointer === null) {
			throw new \RuntimeException("no current task");
		}
		return $this->tasks[$this->pointer];
	}

	#[\Override]
	public function getCurrentIncrement(): TaskEnvelope {
		$current = $this->getCurrent();
		$this->increment();
	return $current;
	}

	#[\Override]
	public function increment(): void {
		if($this->pointer === null) {
			throw new \RuntimeException("pointer unexpectedly null");
		}
		if($this->pointer == $this->count - 1) {
			$this->pointer = 0;
		return;
		}
	$this->pointer++;
	}

	public function isEmpty(): bool {
		return $this->count == null;
	}

	private function modifyPointer(int $key): void {
		if($this->pointer === null) {
			throw new \RuntimeException("pointer unexpectedly null");
		}
		if($this->pointer>$key) {
			$this->pointer--;
		}
		if($this->pointer == $this->count - 1 ) {
			$this->pointer = 0;
		}
	}
	
	#[\Override]
	public function remove(TaskEnvelope $task): void {
		$new = array();
		foreach($this->tasks as $key => $value) {
			if($value === $task) {
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

	#[\Override]
	public function getItem(int $item): TaskEnvelope {
		return $this->tasks[$item];
	}
	
	#[\Override]
	public function getItemByTask(Task $task): TaskEnvelope {
		for($i = 0; $i < $this->getCount(); $i++) {
			if($this->getItem($i)->getTask() === $task) {
				return $this->getItem($i);
			}
		}
	throw new \RuntimeException("Task '". get_class($task)."' not found in '". get_class($this)."'");
	}

	#[\Override]
	public function hasItemByTask(Task $task): bool {
		for($i = 0; $i < $this->getCount(); $i++) {
			if($this->getItem($i)->getTask() === $task) {
				return true;
			}
		}
	return false;
	}
}
