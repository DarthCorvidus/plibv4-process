<?php
class Main implements InputListener, ProcessListener {
	public function __construct() {
		Dispatch::init();
	}
	
	/*
	 * In many cases, it is not necessary to write yourself a dedicated Listener,
	 * which would often consist of only a few or one function. 
	 */
	public function onInput(\Input $input) {
		if($input->getInput()=="quit") {
			exit(0);
		}
		if($input->getInput()=="help") {
			echo "help - this help.".PHP_EOL;
			echo "quit - exit program.".PHP_EOL;
			echo "status - status about process.".PHP_EOL;
		}
		if($input->getInput()=="status") {
			echo "PID:      ".posix_getpid().PHP_EOL;
			echo "Children: ".count(Process::getStack()).PHP_EOL;
		}
		$exp = explode(" ", $input->getInput());
		if(count($exp)<2) {
			return;
		}
		if($exp[0]=="sleep") {
			$sleep = new SleepRunner($exp[1]);
			$process = new Process($sleep);
			$process->addProcessListener($this);
			$process->run();
		}
	}
	
	public function onEnd(\Event $event) {
		echo "Process sleep with pid ".$event->getElement()->getPid()." ended.".PHP_EOL;
	}

	public function onStart(\Event $event) {
		echo "Process sleep started.".PHP_EOL;
	}
	
	
	public function run() {
		echo "Enter »quit« to exit program, »help« for help.".PHP_EOL;
		$input = new Input();
		$input->setInputListener($this);
		$input->run();
	}

}
