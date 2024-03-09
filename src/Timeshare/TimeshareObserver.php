<?php
namespace plibv4\process;
interface TimeshareObserver {
	const STARTED = 1;
	const FINISHED = 2;
	const TERMINATED = 3;
	const PAUSED = 4;
	const RESUMED = 5;
	function onAdd(Timeshare $timeshare, Timeshared $timeshared): void;
	function onStart(Timeshare $timeshare, Timeshared $timeshared): void;
	function onRemove(Timeshare $timeshare, Timeshared $timeshared, int $status): void;
}
