<?php
namespace plibv4\Timeshare;
interface Timeshared {
	/**
	 * Regular, first start of process.
	 * @return void
	 */
	function start(): void;
	
	/**
	 * Central loop of the process. true if it should continue for another
	 * round, false if it is done.
	 * @return bool
	 */
	function loop(): bool;
	/**
	 * Process will be paused, but may resume later.
	 * @return void
	 */
	function pause(): void;
	/**
	 * Process resumes from a pause
	 * @return void
	 */
	function resume(): void;
	
	/**
	 * Process is finished. Although the process 'knows' by itself that it has
	 * finished, I think this will allow some more elegant coding, by putting
	 * any cleanup into finish() instead of cluttering up loop().
	 * @return void
	 */
	function finish(): void;
	/**
	 * Process gets terminated from the outside. Process is expected to end in
	 * an orderly manner.
	 * Should return true if the instance is finished, return if it needs to
	 * continue.
	 * @return boolean True if done, false if not
	 */
	function terminate(): bool;
	/**
	 * Process gets terminated from the outside, but is expected to end next to
	 * immediately.
	 * @return void
	 */
	function kill(): void;
}