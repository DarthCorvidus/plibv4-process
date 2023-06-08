<?php
class Message {
	private $message;
	private $ppid;
	private $pid;
	private $time;
	function __construct(string $message) {
		$this->message = $message;
		$this->pid = posix_getpid();
		$this->time = time();
		$this->ppid = posix_getppid();
	}
	
	function getMessage() {
		return $this->message;
	}
	
	function getSourcePID(): int {
		return $this->pid;
	}
	
	function getTime(): int {
		return $this->time;
	}
}