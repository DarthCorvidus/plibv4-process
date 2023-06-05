<?php
class Main implements ProcessListener {
	private $queue;
	private $inputProcess;
	public function __construct() {
		#Dispatch::init();
		pcntl_signal(SIGALRM, array($this, "onAlarm"));
		pcntl_async_signals(true);
		$this->queue = new SysVQueue(451);
		$this->queue->clear();
		$runner = new InputRunner();
		$this->inputProcess = new Process($runner);
		$this->inputProcess->addProcessListener($this);
	}
	
	public function onAlarm(int $signal, array $info) {
		if($this->queue->hasMessage()) {
			$this->onMessage($this->queue->getMessage());
		}
		if(Event::hasEvent()) {
			$event = Event::receive();
			$event->getElement()->triggerListener($event);
		return;
		}
		
	}
	
	/*
	 * In many cases, it is not necessary to write yourself a dedicated Listener,
	 * which would often consist of only a few or one function. 
	 */
	public function onMessage(Message $message) {
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
			echo "Children: ".count(Process::getStack()).PHP_EOL;
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
	
	public function onEnd(\Event $event) {
		if($event->getElement()->getRunnerName()=="SleepRunner") {
			echo "Process '".$event->getElement()->getRunnerName()."' with pid ".$event->getElement()->getPid()." ended.".PHP_EOL;
		}
	}

	public function onStart(\Event $event) {
		if($event->getElement()->getRunnerName()=="SleepRunner") {
			echo "Process sleep started.".PHP_EOL;
		}
		if($event->getElement()->getRunnerName()=="InputRunner") {
			echo "Process input started.".PHP_EOL;
		}
	}
	
	
	public function run() {
		echo "Enter »quit« to exit program, »help« for help.".PHP_EOL;
		$this->inputProcess->runAndWait();
	}

}
