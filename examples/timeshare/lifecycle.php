#!/usr/bin/env php
<?php
use plibv4\process\examples\lifecycle\Main;
require_once __DIR__.'/../../vendor/autoload.php';
foreach(glob(__DIR__."/lifecycle/*.php") as $value) {
	require_once $value;
}
$main = new Main();
$main->run();