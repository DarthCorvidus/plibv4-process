<?php
class InputRunner implements Runner {
	private $message;
	public function __construct() {
		$this->message = new SysVQueue(451);
	}
	
	public function run() {
		while(TRUE) {
			echo "> ";
			$input = trim(fgets(STDIN));
			if($input=="") {
				continue;
			}
			$this->message->sendHyperwave($input, 1, posix_getppid());
			/*
			 * Process stops itself - this allows Main to print its output
			 * before resuming the process with SIGCONT.
			 * Reason: the new prompt will appear below the output of Main.
			 */
			posix_kill(posix_getpid(), SIGSTOP);
		}
	}

}
