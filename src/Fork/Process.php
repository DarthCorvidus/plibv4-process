<?php
class Process implements SignalHandler {
	private $runner;
	private $listener;
	private $pid;
	private $signal;
	private $paused = FALSE;
	private static $stack = array();
	function __construct(Runner $runner) {
		$this->runner = $runner;
		$this->signal = Signal::get();
	}
	
	public function onSignal(int $signal, array $info) {
		/**
		 * SIGCHLD is sent on SIGSTOP/SIGCONT as well. We want to call onEnd
		 * only when the process has exited.
		 * TODO: the exit code should be used too.
		 */
		$result = pcntl_waitpid($this->pid, $status, WNOHANG);
		if($result==$this->getPid() && $this->listener!=NULL) {
			$this->listener->onEnd($this);
		}
		#print_r($info);
	}
	
	public function getRunnerName(): string {
		return get_class($this->runner);
	}
	
	public function getRunner(): Runner {
		return $this->runner;
	}
	
	public function getPid() {
		return $this->pid;
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
			$this->signal->addSignalHandler(SIGTERM, $this);
			$this->runner->run();
			exit(0);
		}
		if($pid!==0) {
			$this->pid = $pid;
			if($this->listener!=NULL) {
				$this->listener->onStart($this);
			}
			#Event::send(new Event($this, "onStart"));
			#Process::addStack($this);
			/*
			 * Add a signal handler that reacts to SIGCHILD. This will overwrite
			 * any existing signal handler - this does not matter regarding
			 * Process as such, since the handler will be the same for every
			 * process, but it will of course overwrite any handler set by the
			 * user of this library.
			 */
			$this->signal->addSignalHandler(SIGCHLD, $this);
			#pcntl_signal(SIGCHLD, array("Process", "childHandler"));
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
