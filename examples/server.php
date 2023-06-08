#!/usr/bin/env php
<?php
error_reporting(E_ALL);
require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/Server/Server.php';
require_once __DIR__.'/Server/RunnerServer.php';
try {
	$server = new Server();
	$server->run();
} catch (RuntimeException $e) {
	echo $e->getMessage().PHP_EOL;
}