<?php
class Counter implements plibv4\Timeshare\Timeshared {
	private int $max = 0;
	private $count = 0;
	function __construct(int $max) {
		$this->max = $max;
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

	public function terminate(): void {
	}
}
