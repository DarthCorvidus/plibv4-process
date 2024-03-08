<?php
namespace plibv4\process;
interface Timeshared {
	/**
	 * Regular, first start of process.
	 * @return void
	 */
	function __tsStart(): void;
	
	/**
	 * Central loop of the process. true if it should continue for another
	 * round, false if it is done.
	 * @return bool
	 */
	function __tsLoop(): bool;
	/**
	 * Process will be paused, but may resume later.
	 * @return void
	 */
	function __tsPause(): void;
	/**
	 * Process resumes from a pause
	 * @return void
	 */
	function __tsResume(): void;
	
	/**
	 * Process is finished. Although the process 'knows' by itself that it has
	 * finished, I think this will allow some more elegant coding, by putting
	 * any cleanup into finish() instead of cluttering up loop().
	 * @return void
	 */
	function __tsFinish(): void;
	/**
	 * Process gets terminated from the outside. Process is expected to end in
	 * an orderly manner.
	 * Should return true if the instance is finished, return if it needs to
	 * continue.
	 * @return boolean True if done, false if not
	 */
	function __tsTerminate(): bool;
	/**
	 * Process gets terminated from the outside, but is expected to end next to
	 * immediately.
	 * @return void
	 */
	function __tsKill(): void;
}