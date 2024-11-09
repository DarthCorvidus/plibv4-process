<?php
namespace plibv4\process;
/**
 * Interface that designes a Task which is expected by Schedulers.
 * All methods need to be public, but should not be called manually, hence the
 * prefixed __ts in method names.
 */
interface Task {
	/**
	 * Will be called only once when Task is started and is called on the first
	 * iteration.
	 * @return void
	 */
	function __tsStart(Scheduler $sched): void;
	
	/**
	 * Central loop of the task. true if it should continue for another
	 * round, false if it is done.
	 * @return bool
	 */
	function __tsLoop(Scheduler $sched): bool;
	/**
	 * Task will be paused, but may resume later. You do not need to keep track
	 * of the task being paused, as __tsLoop will not be called when a Task is
	 * paused.
	 * @return void
	 */
	function __tsPause(Scheduler $sched): void;
	/**
	 * Task is no longer paused an __tsLoop will be called again.
	 * @return void
	 */
	function __tsResume(Scheduler $sched): void;
	
	/**
	 * Task was finished as planned, ie __tsLoop returned false.
	 * @return void
	 */
	function __tsFinish(Scheduler $sched): void;
	/**
	 * Task gets terminated from the outside. Task is expected to end in
	 * an orderly manner.
	 * If a task is ready to terminate, return true. Tasks may defer their
	 * termination by returning false, but will be killed eventually.
	 * @return boolean True if done, false if not
	 */
	function __tsTerminate(Scheduler $sched): bool;
	/**
	 * Task gets terminated from the outside, but is expected to end next to
	 * immediately.
	 * @return void
	 */
	function __tsKill(Scheduler $sched): void;
	/**
	 * Task threw an error, which gets fed back to the task via __tsError along
	 * with the step that caused the error (START, LOOP...).
	 * @param \Exception $e
	 * @param int $step
	 * @return void
	 */
	function __tsError(Scheduler $sched, \Exception $e, int $step): void;
}
