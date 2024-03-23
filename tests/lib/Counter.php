<?php
class Counter implements plibv4\process\Timeshared {
	private int $max = 0;
	private int $count = 0;
	public int $terminated = 0;
	public int $started = 0;
	public int $finished = 0;
	private int $modulo = 1;
	private int $exceptionOn = 0;
	public int $exceptionThrown = 0;
	public Exception $exceptionReceived;
	public int $exceptionStep = 0;
	public bool $exceptionStart = false;
	public bool $exceptionFinish = false;
	public bool $exceptionPause = false;
	public bool $exceptionResume = false;
	function __construct(int $max, int $modulo = 1) {
		$this->max = $max;
		$this->modulo = $modulo;
	}
	
	function exceptionOn(int $i) {
		$this->exceptionOn = $i;
	}
	
	public function getCount(): int {
		return $this->count;
	}

	public function __tsFinish(): void {
		if($this->exceptionFinish) {
			$this->exceptionThrown++;
			throw new \RuntimeException("exception at finish.");
		}
		$this->finished++;
	}

	public function __tsKill(): void {
		
	}

	public function __tsLoop(): bool {
		$this->count++;
		if($this->count == $this->exceptionOn) {
			$this->exceptionThrown++;
			throw new RuntimeException("This exception is an expection.");
		}
	return $this->count < $this->max;
	}

	public function __tsPause(): void {
		if($this->exceptionPause) {
			$this->exceptionThrown++;
			throw new \RuntimeException("exception at pause.");
		}
	}

	public function __tsResume(): void {
		if($this->exceptionResume) {
			$this->exceptionThrown++;
			throw new \RuntimeException("exception at resume.");
		}
	}

	public function __tsStart(): void {
		if($this->exceptionStart) {
			$this->exceptionThrown++;
			throw new \RuntimeException("exception at start");
		}
		$this->started++;
	}

	public function __tsTerminate(): bool {
		$this->terminated++;
		return $this->count % $this->modulo == 0;
	}
	
	public function __tsError(\Exception $e, int $step): void {
		$this->exceptionReceived = $e;
		$this->exceptionStep = $step;
	}
}
