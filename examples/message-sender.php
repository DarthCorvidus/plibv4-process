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

$queue = new SysVQueue(451);
if(isset($argv[1]) && isset($argv[2])) {
	$target = $argv[2];
	echo "Sending to process ".$target.PHP_EOL;
	$queue->sendHyperwave($argv[1], 1, $target);
	exit(0);
}
if(isset($argv[1])) {
	$queue->sendMessage($argv[1], 1);
	exit(0);
}
