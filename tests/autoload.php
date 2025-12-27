<?php
require_once __DIR__."/../vendor/autoload.php";
$files = glob(__DIR__."/lib/*.php");
if($files === false) {
	throw new RuntimeException("unable to load test library");
}
foreach($files as $value) {
	/** @psalm-suppress UnresolvableInclude */
	require_once $value;
}