<?php
namespace plibv4\process;
interface Strategy {
	function getCount(): int;
	function getItem(int $i): TaskEnvelope;
	function getCurrent(): TaskEnvelope;
	function getCurrentIncrement(): TaskEnvelope;
	function increment(): void;
	function add(TaskEnvelope $timeshared);
	function remove(TaskEnvelope $timeshared);
}
