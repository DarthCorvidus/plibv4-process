<?php
namespace plibv4\process;
interface TimeshareObserver {
	function onAdd(Timeshare $timeshare, Task $Task): void;
	function onStart(Timeshare $timeshare, Task $Task): void;
	function onRemove(Timeshare $timeshare, Task $Task, int $step): void;
	function onError(Timeshare $timeshare, Task $Task, \Exception $e, int $step): void;
	function onPause(Timeshare $timeshare, Task $Task): void;
	function onResume(Timeshare $timeshare, Task $Task): void;
}
