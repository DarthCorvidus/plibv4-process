<?php
class Counter implements plibv4\process\Timeshared {
	private int $max = 0;
	private int $count = 0;
	public int $terminated = 0;
	public int $started = 0;
	public int $finished = 0;
	private int $modulo = 1;
	function __construct(int $max, int $modulo = 1) {
		$this->max = $max;
		$this->modulo = $modulo;
	}
	
	public function getCount(): int {
		return $this->count;
	}

	public function __tsFinish(): void {
		$this->finished++;
	}

	public function __tsKill(): void {
		
	}

	public function __tsLoop(): bool {
		$this->count++;
	return $this->count < $this->max;
	}

	public function __tsPause(): void {
		
	}

	public function __tsResume(): void {
		
	}

	public function __tsStart(): void {
		$this->started++;
	}

	public function __tsTerminate(): bool {
		$this->terminated++;
		return $this->count % $this->modulo == 0;
	}
}
