#!/usr/bin/env php 
<?php
require_once __DIR__.'/../vendor/autoload.php';
require_once(__DIR__."/input/Input.php");
require_once(__DIR__."/input/ExampleInputListener.php");

echo "Enter »quit« to exit program, »help« for help.".PHP_EOL;
Dispatch::init();
$input = new Input();
$input->setInputListener(new ExampleInputListener());
$input->run();
