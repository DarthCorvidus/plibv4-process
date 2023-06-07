#!/usr/bin/env php
<?php
require_once __DIR__.'/../vendor/autoload.php';

class Listen implements MessageListener {
	private $queue;
	private $signal;
	function __construct() {
		pcntl_async_signals(true);
		$this->queue = new SysVQueue(451);
		$this->signal = Signal::get();
		$this->queue->addListener($this->signal, $this);
	}

	public function onMessage(\Message $message) {
		echo "Received message ".$message->getMessage();
	}

	function run() {
		echo "Listening with PID ".posix_getpid().PHP_EOL;
		while(true) {
			sleep(10);
		}
	}
}

$listen = new Listen();
$listen->run();