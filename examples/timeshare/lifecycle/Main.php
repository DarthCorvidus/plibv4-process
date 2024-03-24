<?php
namespace plibv4\process\examples\lifecycle;
use plibv4\process\Timeshare;
use plibv4\process\Scheduler;
use plibv4\process\TimeshareObserver;
class Main implements TimeshareObserver, InputObserver {
	private Scheduler $scheduler;
	private Deca $deca;
	private bool $paused = false;
	private Input $input;
	function __construct() {
		$this->scheduler = new Timeshare();
		$this->scheduler->addTimeshareObserver($this);
		echo "Task lifecycle demonstration. Please enter a number from 1 - 10.".PHP_EOL;
		echo "A number higher than 10 will throw an exception once 10 is surpassed.".PHP_EOL;
		echo "x: quit, p: pause/resume.".PHP_EOL;
		echo "> ";
		/*
		 * We quit if casting to int results in zero.
		 */
		$number = (int)fgets(STDIN);
		if($number <= 0) {
			return;
		}
		$this->deca = new Deca($number);
		$this->scheduler->addTask($this->deca);
		$this->input = new Input($this);
		$this->scheduler->addTask($this->input);
	}
	
	function run() {
		/**
		 * Run the scheduler until no more tasks are left.
		 */
		$this->scheduler->run();
	}

	public function onAdd(Scheduler $scheduler, \plibv4\process\Task $task): void {
		echo "TimeshareObserver::onAdd(), ".get_class($task)." called.".PHP_EOL;
	}

	public function onError(Scheduler $scheduler, \plibv4\process\Task $task, \Exception $e, int $step): void {
		echo "TimeshareObserver::onError(), ".get_class($task)." called, step ".$step.".".PHP_EOL;
	}

	public function onPause(Scheduler $scheduler, \plibv4\process\Task $task): void {
		echo "TimeshareObserver::onPause(), ".get_class($task)." called.".PHP_EOL;
	}

	public function onRemove(Scheduler $scheduler, \plibv4\process\Task $task, int $step): void {
		echo "TimeshareObserver::onRemove() called, ". get_class($task)." step ".$step.".".PHP_EOL;
		/**
		 * If task Deca was removed from the scheduler, terminate Input as well.
		 */
		if($task === $this->deca) {
			$this->scheduler->terminate($this->input);
		}
	}

	public function onResume(Scheduler $scheduler, \plibv4\process\Task $task): void {
		echo "TimeshareObserver::onResume(), ".get_class($task)." called.".PHP_EOL;
	}

	public function onStart(Scheduler $scheduler, \plibv4\process\Task $task): void {
		echo "TimeshareObserver::onStart(), ".get_class($task)." called.".PHP_EOL;
	}
	/**
	 * Handle input.
	 * @param Input $input
	 * @param string $c
	 * @return type
	 */
	public function onInput(Input $input, string $c) {
		/**
		 * Terminate both tasks on 'x'.
		 */
		if($c=="x") {
			$this->scheduler->terminate($input);
			$this->scheduler->terminate($this->deca);
		return;
		}
		/**
		 * pause or unpause Deca on 'p'
		 */
		if($c=="p" && !$this->paused) {
			$this->scheduler->pause($this->deca);
			$this->paused = true;
		return;
		}
		if($c=="p" && $this->paused) {
			$this->scheduler->resume($this->deca);
			$this->paused = false;
		return;
		}
	}
}
