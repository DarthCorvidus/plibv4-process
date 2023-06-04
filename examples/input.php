#!/usr/bin/env php 
<?php
require_once __DIR__.'/../vendor/autoload.php';
require_once(__DIR__."/input/Input.php");
require_once(__DIR__."/input/Main.php");

$main = new Main();
$main->run();