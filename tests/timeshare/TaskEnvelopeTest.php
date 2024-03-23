<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use plibv4\process\TimeshareObservers;
use plibv4\process\TimeshareObserver;
use plibv4\process\TaskEnvelope;
use plibv4\process\Timeshare;
use plibv4\process\Timeshared;
class TaskEnvelopeTest extends TestCase {
	function testConstruct() {
		$observers = new TimeshareObservers();
		$to = new TrackObserver();
		$observers->addTimeshareObserver($to);
		$scheduler = new Timeshare();
		$task = new Counter(20);
		$envelope = new TaskEnvelope($scheduler, $task, $observers);
		$this->assertInstanceOf(TaskEnvelope::class, $envelope);
		$to->onStartNotCalled();
	}
	
	function testStart() {
		$observers = new TimeshareObservers();
		$to = new TrackObserver();
		$observers->addTimeshareObserver($to);
		$scheduler = new Timeshare();
		$task = new Counter(20);
		$envelope = new TaskEnvelope($scheduler, $task, $observers);
		$envelope->loop();
		$to->onStartCalled($scheduler, $task, 1);
		$this->assertSame(0, $task->getCount());
	}

	function testStartError() {
		$observers = new TimeshareObservers();
		$to = new TrackObserver();
		$observers->addTimeshareObserver($to);
		$scheduler = new Timeshare();
		$task = new Counter(20);
		$task->exceptionStart = true;
		$envelope = new TaskEnvelope($scheduler, $task, $observers);
		$envelope->loop();
		$to->onErrorCalled($scheduler, $task, $task->exceptionReceived, Timeshare::START, 1);
		$this->assertSame(0, $task->getCount());
	}
	
	function testLoopFirst() {
		$observers = new TimeshareObservers();
		$scheduler = new Timeshare();
		$task = new Counter(20);
		$envelope = new TaskEnvelope($scheduler, $task, $observers);
		$envelope->loop();
		$envelope->loop();
		$this->assertSame(1, $task->started);
		$this->assertSame(1, $task->getCount());
	}

	function testLoopRun() {
		$observers = new TimeshareObservers();
		$scheduler = new Timeshare();
		$task = new Counter(20);
		$envelope = new TaskEnvelope($scheduler, $task, $observers);
		$looped = 0;
		while($envelope->loop()) {
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
		$to = new TrackObserver();
		$observers->addTimeshareObserver($to);
		$scheduler = new Timeshare();
		$task = new Counter(20);
		$task->exceptionOn(15);
		$envelope = new TaskEnvelope($scheduler, $task, $observers);
		while($envelope->loop()) {
			
		}
		$to->onErrorCalled($scheduler, $task, $task->exceptionReceived, Timeshare::LOOP, 1);
		$this->assertSame("This exception is an expection.", $task->exceptionReceived->getMessage());
		$this->assertSame(15, $task->getCount());
	}
	
	function testPause() {
		$observers = new TimeshareObservers();
		$to = new TrackObserver();
		$observers->addTimeshareObserver($to);
		$scheduler = new Timeshare();
		$task = new Counter(20);
		$envelope = new TaskEnvelope($scheduler, $task, $observers);
		for($i = 0; $i<=10;$i++) {
			$envelope->loop();
		}
		$envelope->pause();
		$to->onPauseCalled($scheduler, $task, 1);
		$this->assertSame(10, $task->getCount());
		for($i = 0; $i<=10;$i++) {
			$envelope->loop();
		}
		$this->assertSame(10, $task->getCount());
	}
	
	function testPauseError() {
		$observers = new TimeshareObservers();
		$to = new TrackObserver();
		$observers->addTimeshareObserver($to);
		$scheduler = new Timeshare();
		$task = new Counter(20);
		$task->exceptionPause = true;
		$envelope = new TaskEnvelope($scheduler, $task, $observers);
		for($i = 0; $i<=10;$i++) {
			$envelope->loop();
		}
		$envelope->pause();
		$to->onErrorCalled($scheduler, $task, $task->exceptionReceived, Timeshare::PAUSE, 1);
	}

	function testResume() {
		$observers = new TimeshareObservers();
		$to = new TrackObserver();
		$observers->addTimeshareObserver($to);
		$scheduler = new Timeshare();
		$task = new Counter(20);
		$envelope = new TaskEnvelope($scheduler, $task, $observers);
		for($i = 0; $i<=10;$i++) {
			$envelope->loop();
		}
		$envelope->pause();
		$to->onPauseCalled($scheduler, $task, 1);
		$envelope->resume();
		$to->onResumeCalled($scheduler, $task, 1);
		$this->assertSame(10, $task->getCount());
		while($envelope->loop()) {}
		$this->assertSame(20, $task->getCount());
	}
	
	function testResumeError() {
		$observers = new TimeshareObservers();
		$to = new TrackObserver();
		$observers->addTimeshareObserver($to);
		$scheduler = new Timeshare();
		$task = new Counter(20);
		$task->exceptionResume = true;
		$envelope = new TaskEnvelope($scheduler, $task, $observers);
		for($i = 0; $i<=10;$i++) {
			$envelope->loop();
		}
		$envelope->pause();
		$envelope->resume();
		$to->onErrorCalled($scheduler, $task, $task->exceptionReceived, Timeshare::RESUME, 1);
	}
}
