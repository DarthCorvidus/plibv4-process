<?php
class Message {
	private string $message;
	private int $ppid;
	private int $pid;
	private int $time;
	function __construct(string $message) {
		$this->message = $message;
		$this->pid = posix_getpid();
		$this->time = time();
		$this->ppid = posix_getppid();
	}
	
	function getMessage(): string {
		return $this->message;
	}
	
	function getSourcePID(): int {
		return $this->pid;
	}
	
	function getTime(): int {
		return $this->time;
	}
}