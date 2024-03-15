<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use plibv4\process\TimeshareObservers;
use plibv4\process\TimeshareObserver;
use plibv4\process\TaskEnvelope;
use plibv4\process\Timeshare;
use plibv4\process\Timeshared;
class TaskEnvelopeTest extends TestCase implements TimeshareObserver {
	private int $started = 0;
	private int $errors = 0;
	private int $lastStep = 0;
	private ?\Exception $lastException = null;
	function tearDown() {
		parent::tearDown();
		$this->started = 0;
		$this->errors = 0;
	}
	function testConstruct() {
		$observers = new TimeshareObservers();
		$observers->addTimeshareObserver($this);
		$scheduler = new Timeshare();
		$task = new Counter(20);
		$envelope = new TaskEnvelope($scheduler, $task, $observers);
		$this->assertInstanceOf(TaskEnvelope::class, $envelope);
		$this->assertSame(0, $this->started);
	}
	
	function testStart() {
		$observers = new TimeshareObservers();
		$observers->addTimeshareObserver($this);
		$scheduler = new Timeshare();
		$task = new Counter(20);
		$envelope = new TaskEnvelope($scheduler, $task, $observers);
		$envelope->__tsLoop();
		$this->assertSame(1, $task->started);
		$this->assertSame(1, $this->started);
		$this->assertSame(0, $task->getCount());
	}

	function testStartError() {
		$observers = new TimeshareObservers();
		$observers->addTimeshareObserver($this);
		$scheduler = new Timeshare();
		$task = new Counter(20);
		$task->exceptionStart = true;
		$envelope = new TaskEnvelope($scheduler, $task, $observers);
		$envelope->__tsLoop();
		$this->assertSame(0, $task->started);
		$this->assertSame(0, $this->started);
		$this->assertSame(1, $this->errors);
		$this->assertSame(Timeshare::START, $this->lastStep);
		$this->assertInstanceOf(\RuntimeException::class, $this->lastException);
		$this->assertSame("exception at start", $this->lastException->getMessage());
		$this->assertSame(0, $task->getCount());
	}
	
	function testLoopFirst() {
		$observers = new TimeshareObservers();
		$scheduler = new Timeshare();
		$task = new Counter(20);
		$envelope = new TaskEnvelope($scheduler, $task, $observers);
		$envelope->__tsLoop();
		$envelope->__tsLoop();
		$this->assertSame(1, $task->started);
		$this->assertSame(1, $task->getCount());
	}

	function testLoopRun() {
		$observers = new TimeshareObservers();
		$scheduler = new Timeshare();
		$task = new Counter(20);
		$envelope = new TaskEnvelope($scheduler, $task, $observers);
		$looped = 0;
		while($envelope->__tsLoop()) {
			$looped++;
		}
		/**
		 * One $loop increment is used for start, then 19 increments to reach 20.
		 */
		$this->assertSame(20, $looped);
		$this->assertSame(1, $task->started);
		$this->assertSame(20, $task->getCount());
	}

	function testLoopError() {
		$observers = new TimeshareObservers();
		$observers->addTimeshareObserver($this);
		$scheduler = new Timeshare();
		$task = new Counter(20);
		$task->exceptionOn(15);
		$envelope = new TaskEnvelope($scheduler, $task, $observers);
		while($envelope->__tsLoop()) {
			
		}
		$this->assertSame(1, $task->started);
		$this->assertSame(1, $this->started);
		$this->assertSame(1, $this->errors);
		$this->assertSame(Timeshare::LOOP, $this->lastStep);
		$this->assertInstanceOf(\RuntimeException::class, $this->lastException);
		$this->assertSame("This exception is an expection.", $this->lastException->getMessage());
		$this->assertSame(15, $task->getCount());
	}

	public function onAdd(Timeshare $timeshare, Timeshared $timeshared): void {
		
	}

	public function onError(Timeshare $timeshare, Timeshared $timeshared, \Exception $e, int $step): void {
		$this->errors++;
		$this->lastStep = $step;
		$this->lastException = $e;
	}

	public function onRemove(Timeshare $timeshare, Timeshared $timeshared, int $step): void {
		
	}

	public function onStart(Timeshare $timeshare, Timeshared $timeshared): void {
		$this->started++;
	}
}
