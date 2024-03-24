<?php
namespace plibv4\process;
interface TimeshareObserver {
	function onAdd(Scheduler $scheduler, Task $task): void;
	function onStart(Scheduler $scheduler, Task $task): void;
	function onRemove(Scheduler $scheduler, Task $task, int $step): void;
	function onError(Scheduler $scheduler, Task $task, \Exception $e, int $step): void;
	function onPause(Scheduler $scheduler, Task $task): void;
	function onResume(Scheduler $scheduler, Task $task): void;
}
