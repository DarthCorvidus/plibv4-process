<?php
namespace plibv4\process\examples\lifecycle;
use plibv4\process\Task;
class Deca implements Task {
	private int $max;
	private int $current = 0;
	private float $last;
	function __construct(int $max) {
		$this->max = $max;
		$this->last = microtime(true);
	}

	public function __tsError(\Exception $e, int $step): void {
		echo "Called ".get_class($this)."::__tsError()".PHP_EOL;
	}

	public function __tsFinish(): void {
		
	}

	public function __tsKill(): void {
		
	}

	public function __tsLoop(): bool {
		$now = microtime(true);
		/**
		 * If the time delta is below one second, return true without doing
		 * anything.
		 */
		if(floor($now-$this->last)>0) {
			$this->last = $now;
		} else {
			return true;
		}
		// Otherwise increment current by one.
		if($this->current<$this->max) {
			$this->current++;
		}
		/*
		 * Throw exception if current exceeds 10, which triggers __tsError and
		 * TaskObservers::onError();
		 */
		if($this->current>10) {
			throw new \Exception("Overflow!");
		}
		echo $this->current.PHP_EOL;
	return $this->current<$this->max;
	}

	public function __tsPause(): void {
		
	}

	public function __tsResume(): void {
		
	}

	public function __tsStart(): void {
		
	}

	public function __tsTerminate(): bool {
		return true;
	}
}
