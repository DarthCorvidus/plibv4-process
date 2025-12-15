<?php
class Process implements SignalHandler {
	private Runner $runner;
	private ?ProcessListener $listener = null;
	private ?int $pid = null;
	private Signal $signal;
	function __construct(Runner $runner) {
		if(pcntl_async_signals()!==TRUE) {
			throw new ErrorException("Process needs pcntl_async_signal() to be TRUE.");
		}
		$this->runner = $runner;
		$this->signal = Signal::get();
	}
	
	public function onSignal(int $signal, array $info): void {
		/**
		 * SIGCHLD is sent on SIGSTOP/SIGCONT as well. We want to call onEnd
		 * only when the process has exited.
		 * TODO: the exit code should be used too.
		 */
		$result = pcntl_waitpid($this->getPid(), $status, WNOHANG);
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
	
	public function getPid(): int {
		if($this->pid === null) {
			throw new Exception("no pid yet, process was not forked.");
		}
		return $this->pid;
	}
	
	function addProcessListener(ProcessListener $listener): void {
		$this->listener = $listener;
	}

	public function sigStop(): void {
		posix_kill($this->getPid(), SIGSTOP);
	}
	
	public function sigCont(): void {
		posix_kill($this->getPid(), SIGCONT);
	}
	
	public function sigTerm(): void {
		posix_kill($this->getPid(), SIGTERM);
	}
	
	public function triggerListener(\Event $event): void {
		if($this->listener===null) {
			return;
		}
		if($event->getEventId()==="onStart") {
			$this->listener->onStart($this);
		}

		if($event->getEventId()==="onEnd") {
			$this->listener->onEnd($this);
		}
	}
	
	/**
	 * run() is fire and forget: the process is forked and run ends, so not to
	 * block the parent process.
	 * @throws Exception
	 */
	function run(): void {
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
	/**
	 * runAndWait spawns a thread and waits for it to finish, ie blocking the
	 * calling process.
	 */
	function runAndWait(): void {
		$this->run();
		$status = 0;
		while(true) {
			$result = pcntl_waitpid($this->getPid(), $status, WNOHANG);
			if($result == -1 or $result > 0) {
				exit(0);
			}
		}
	}
}
