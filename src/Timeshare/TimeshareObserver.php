<?php
namespace plibv4\process;
interface TimeshareObserver {
	function onAdd(Timeshare $timeshare, Timeshared $timeshared): void;
	function onStart(Timeshare $timeshare, Timeshared $timeshared): void;
	function onRemove(Timeshare $timeshare, Timeshared $timeshared, int $step): void;
	function onError(Timeshare $timeshare, Timeshared $timeshared, \Exception $e, int $step): void;
}
