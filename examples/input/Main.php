<?php
class Main implements ProcessListener, SignalHandler {
	private $queue;
	private $inputProcess;
	private $signal;
	public function __construct() {
		#Dispatch::init();
		$this->signal = Signal::get();
		$this->signal->addSignalHandler(SIGALRM, $this);
		pcntl_async_signals(true);
		$this->queue = new SysVQueue(451);
		$this->queue->clear();
		$runner = new InputRunner();
		$this->inputProcess = new Process($runner);
		$this->inputProcess->addProcessListener($this);
	}
	
	public function onSignal(int $signal, array $info): void {
		if($this->queue->hasMessage() && $signal==SIGALRM) {
			$this->onMessage($this->queue->getMessage());
		}
	}
	
	/*
	 * In many cases, it is not necessary to write yourself a dedicated Listener,
	 * which would often consist of only a few or one function. 
	 */
	public function onMessage(Message $message): void {
		if($message->getMessage()=="quit") {
			$this->inputProcess->sigTerm();
			exit(0);
		}
		if($message->getMessage()=="help") {
			echo "help - this help.".PHP_EOL;
			echo "quit - exit program.".PHP_EOL;
			echo "status - status about process.".PHP_EOL;
			$this->inputProcess->sigCont();
			return;
		}
		if($message->getMessage()=="status") {
			echo "PID:      ".posix_getpid().PHP_EOL;
			#echo "Children: ".count(Process::getStack()).PHP_EOL;
			$this->inputProcess->sigCont();
			return;
		}
		$exp = explode(" ", $message->getMessage());
		if(count($exp)<2) {
			echo "Unknown command ".$message->getMessage().PHP_EOL;
			$this->inputProcess->sigCont();
			return;
		}
		if($exp[0]=="sleep") {
			$sleep = new SleepRunner($exp[1]);
			$process = new Process($sleep);
			$process->addProcessListener($this);
			$process->run();
			$this->inputProcess->sigCont();
			return;
		}
		echo "Unknown command ".$message->getMessage().PHP_EOL;
		$this->inputProcess->sigCont();
	}
	
	public function onEnd(Process $process): void {
		if($process->getRunnerName() instanceof SleepRunner) {
			echo "Process '".$process->getRunnerName()."' with pid ".$process->getPid()." ended.".PHP_EOL;
		}
	}

	public function onStart(Process $process): void {
		if($process->getRunner() instanceof SleepRunner) {
			echo "Process sleep started with pid ".$process->getPid().".".PHP_EOL;
		}
		if($process->getRunner() instanceof InputRunner) {
			echo "Process input started.".PHP_EOL;
		}
	}
	
	
	public function run(): void {
		echo "Enter »quit« to exit program, »help« for help.".PHP_EOL;
		$this->inputProcess->runAndWait();
	}

}
