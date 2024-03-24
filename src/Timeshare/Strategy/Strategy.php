<?php
namespace plibv4\process;
interface Strategy {
	function getCount(): int;
	function getItem(int $i): TaskEnvelope;
	function getCurrent(): TaskEnvelope;
	function getCurrentIncrement(): TaskEnvelope;
	function increment(): void;
	function add(TaskEnvelope $Task);
	function remove(TaskEnvelope $Task);
	function getItemByTask(Task $Task): TaskEnvelope;
	function hasItemByTask(Task $Task): bool;
}
