<?php
class Stubborn implements \plibv4\process\Timeshared {
	public function __tsFinish(): void {
		
	}

	public function __tsKill(): void {
		
	}

	public function __tsLoop(): bool {
		return true;
	}

	public function __tsPause(): void {
		
	}

	public function __tsResume(): void {
		
	}

	public function __tsStart(): void {
		
	}

	public function __tsTerminate(): bool {
		return false;
	}
	
	public function __tsError(\Exception $e, int $step): void {
		;
	}
}
