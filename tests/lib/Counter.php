<?php
class Counter implements plibv4\process\Timeshared {
	private int $max = 0;
	private int $count = 0;
	public bool $terminated = false;
	public bool $started = false;
	public bool $finished = false;
	private int $modulo = 1;
	function __construct(int $max, int $modulo = 1) {
		$this->max = $max;
		$this->modulo = $modulo;
	}
	
	public function getCount(): int {
		return $this->count;
	}

	public function finish(): void {
		
	}

	public function kill(): void {
		
	}

	public function loop(): bool {
		$this->count++;
	return $this->count < $this->max;
	}

	public function pause(): void {
		
	}

	public function resume(): void {
		
	}

	public function start(): void {
		
	}

	public function terminate(): bool {
		return $this->count % $this->modulo == 0;
	}
}
