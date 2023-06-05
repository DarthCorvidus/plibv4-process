<?php
class Process implements Element {
	private $runner;
	private $listener;
	private $pid;
	private static $stack = array();
	function __construct(Runner $runner) {
		$this->runner = $runner;
	}
	
	public function getRunnerName(): string {
		return get_class($this->runner);
	}
	
	public function getPid() {
		return $this->pid;
	}
	
	private static function addStack(Process $process) {
		self::$stack[$process->getPid()] = $process;
	}
	
	static function getStack(): array {
		return self::$stack;
	}
	
	static function childHandler($sig, $info) {
		$child = $info["pid"];
		if(isset(Process::$stack[$child])) {
			Event::send(new Event(Process::$stack[$child], "onEnd"));
			unset(Process::$stack[$child]);
		}
	}
	
	function addProcessListener(ProcessListener $listener) {
		$this->listener = $listener;
	}

	public function sigStop() {
		posix_kill($this->pid, SIGSTOP);
	}
	
	public function sigCont() {
		posix_kill($this->pid, SIGCONT);
	}
	
	public function sigTerm() {
		posix_kill($this->pid, SIGTERM);
	}
	
	public function triggerListener(\Event $event) {
		if($this->listener==NULL) {
			return;
		}
		if($event->getEventId()=="onStart") {
			$this->listener->onStart($event);
		}

		if($event->getEventId()=="onEnd") {
			$this->listener->onEnd($event);
		}
	}
	
	function run() {
		$pid = pcntl_fork();
		if($pid=="-1") {
			throw new Exception("Unable to fork");
		}
		if($pid==0) {
			$this->runner->run();
			exit(0);
		}
		if($pid!==0) {
			Event::send(new Event($this, "onStart"));
			$this->pid = $pid;
			Process::addStack($this);
			/*
			 * Add a signal handler that reacts to SIGCHILD. This will overwrite
			 * any existing signal handler - this does not matter regarding
			 * Process as such, since the handler will be the same for every
			 * process, but it will of course overwrite any handler set by the
			 * user of this library.
			 */
			pcntl_signal(SIGCHLD, array("Process", "childHandler"));
		}
	}
	
	function runAndWait() {
		$this->run();
		$status = 0;
		while(TRUE) {
			$result = pcntl_waitpid($this->pid, $status, WNOHANG);
			if($result == -1 or $result > 0) {
				exit(0);
			}
		}
	}
}
