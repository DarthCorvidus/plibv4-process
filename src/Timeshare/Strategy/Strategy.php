<?php
namespace plibv4\process;
interface Strategy {
	function getCount(): int;
	function getItem(int $item): TaskEnvelope;
	function getCurrent(): TaskEnvelope;
	function getCurrentIncrement(): TaskEnvelope;
	function increment(): void;
	function add(TaskEnvelope $task): void;
	function remove(TaskEnvelope $task): void;
	function getItemByTask(Task $task): TaskEnvelope;
	function hasItemByTask(Task $task): bool;
}
