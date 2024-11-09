<?php
use plibv4\process\Scheduler;
class Stubborn implements \plibv4\process\Task {
	public function __tsFinish(Scheduler $sched): void {
		
	}

	public function __tsKill(Scheduler $sched): void {
		
	}

	public function __tsLoop(Scheduler $sched): bool {
		return true;
	}

	public function __tsPause(Scheduler $sched): void {
		
	}

	public function __tsResume(Scheduler $sched): void {
		
	}

	public function __tsStart(Scheduler $sched): void {
		
	}

	public function __tsTerminate(Scheduler $sched): bool {
		return false;
	}
	
	public function __tsError(Scheduler $sched, \Exception $e, int $step): void {
		;
	}
}
