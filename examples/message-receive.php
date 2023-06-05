#!/usr/bin/env php
<?php
require_once __DIR__.'/../vendor/autoload.php';

function test($signal, $info) {
	$queue = new SysVQueue(451);
	if($queue->hasMessage()) {
		$message = $queue->getMessage();
		echo $message->getMessage().PHP_EOL;
		return;
	}
}
pcntl_async_signals(true);
pcntl_signal(SIGALRM, "test");
echo "Listening with PID ".posix_getpid().PHP_EOL;
while(true) {
	sleep(10);
}