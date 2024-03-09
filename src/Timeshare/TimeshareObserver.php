<?php
namespace plibv4\process;
interface TimeshareObserver {
	const FINISHED = 1;
	const TERMINATED = 2;
	const LOOP = 3;
	const PAUSE = 4;
	const RESUME = 5;
	const ERROR = 255;
	function onAdd(Timeshare $timeshare, Timeshared $timeshared): void;
	function onStart(Timeshare $timeshare, Timeshared $timeshared): void;
	function onRemove(Timeshare $timeshare, Timeshared $timeshared, int $status): void;
}
