<?php
namespace plibv4\process;
interface TimeshareObserver {
	const START = 1;
	const FINISHED = 2;
	const TERMINATED = 3;
	const LOOP = 4;
	const PAUSE = 5;
	const RESUME = 6;
	const ERROR = 255;
	function onAdd(Timeshare $timeshare, Timeshared $timeshared): void;
	function onStart(Timeshare $timeshare, Timeshared $timeshared): void;
	function onRemove(Timeshare $timeshare, Timeshared $timeshared, int $status): void;
}
