<?php
namespace plibv4\process;
interface Scheduler {
	const START = 1;
	const LOOP = 2;
	const FINISH = 3;
	const TERMINATE = 4;
	const PAUSE = 5;
	const RESUME = 6;
	const KILL = 7;
	const ERROR = 255;
	/**
	 * Adds an observer.
	 * @param TimeshareObserver $observer
	 * @return void
	 */
	public function addTimeshareObserver(TimeshareObserver $observer): void;
	/**
	 * Timeout after which terminated tasks should be killed if they defer.
	 * @param int $seconds
	 * @param int $microseconds
	 * @return void
	 */
	public function setTimeout(int $seconds, int $microseconds): void;
	/**
	 * Returns the timeout in microseconds.
	 * @return int
	 */
	public function getTimeout(): int;
	/**
	 * Number of Tasks managed by a Scheduler.
	 * @return int
	 */
	public function getTaskCount(): int;
	/**
	 * Add a Task to the Scheduler.
	 * @param Task $task
	 * @return void
	 */
	public function addTask(Task $task): void;
	/**
	 * Supposed to return true if a Scheduler manages a certain task.
	 * @param Task $task
	 * @return bool
	 */
	public function hasTask(Task $task): bool;
	/**
	 * Terminate a certain task. Tasks have to be killed if timeout is reached.
	 * @param Task $task
	 * @return void
	 */
	public function terminate(Task $task): void;
	/**
	 * Kill a certain task.
	 * @param Task $task
	 * @return void
	 */
	public function kill(Task $task): void;
	/**
	 * Pause specific task.
	 * @param Task $task
	 * @return void
	 */
	public function pause(Task $task): void;
	/**
	 * Resume specific task
	 * @param Task $task
	 * @return void
	 */
	public function resume(Task $task): void;
	/**
	 * Run the Scheduler until it is out of Tasks to process.
	 * @return void
	 */
	public function run(): void;
}
