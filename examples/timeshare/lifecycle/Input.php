<?php
namespace plibv4\process\examples\lifecycle;
use plibv4\process\Task;
class Input implements Task {
	private InputObserver $inputObserver;
	function __construct(InputObserver $inputObserver) {
		readline_callback_handler_install("", array($this, "readline"));
		stream_set_blocking(STDIN, false);
		$this->inputObserver = $inputObserver;
	}
	
	public function readline() {
		
	}
	
	public function __tsError(\Exception $e, int $step): void {
		
	}

	public function __tsFinish(): void {
		
	}

	public function __tsKill(): void {
		
	}

	public function __tsLoop(): bool {
		$c = fgetc(STDIN);
		$this->inputObserver->onInput($this, $c);
	return true;
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
