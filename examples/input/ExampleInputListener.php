<?php
class ExampleInputListener implements InputListener {
	public function onInput(Input $input) {
		if($input->getInput()=="quit") {
			exit(0);
		}
		if($input->getInput()=="help") {
			echo "help - this help.".PHP_EOL;
			echo "quit - exit program.".PHP_EOL;
			echo "status - status about process.".PHP_EOL;
		}
		if($input->getInput()=="status") {
			echo "PID: ".posix_getpid().PHP_EOL;
		}
	}

}