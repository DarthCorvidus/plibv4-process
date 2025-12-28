<?php
namespace plibv4\process;
use Exception;
final class Stubborn implements Task {
	#[\Override]
	public function __tsFinish(Scheduler $sched): void {
		
	}

	#[\Override]
	public function __tsKill(Scheduler $sched): void {
		
	}

	#[\Override]
	public function __tsLoop(Scheduler $sched): bool {
		return true;
	}

	#[\Override]
	public function __tsPause(Scheduler $sched): void {
		
	}

	#[\Override]
	public function __tsResume(Scheduler $sched): void {
		
	}

	#[\Override]
	public function __tsStart(Scheduler $sched): void {
		
	}

	#[\Override]
	public function __tsTerminate(Scheduler $sched): bool {
		return false;
	}
	
	#[\Override]
	public function __tsError(Scheduler $sched, Exception $e, int $step): void {
		;
	}
}
