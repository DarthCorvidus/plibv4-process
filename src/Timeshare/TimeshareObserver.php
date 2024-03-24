<?php
namespace plibv4\process;
interface TimeshareObserver {
	function onAdd(Timeshare $timeshare, Task $task): void;
	function onStart(Timeshare $timeshare, Task $task): void;
	function onRemove(Timeshare $timeshare, Task $task, int $step): void;
	function onError(Timeshare $timeshare, Task $task, \Exception $e, int $step): void;
	function onPause(Timeshare $timeshare, Task $task): void;
	function onResume(Timeshare $timeshare, Task $task): void;
}
