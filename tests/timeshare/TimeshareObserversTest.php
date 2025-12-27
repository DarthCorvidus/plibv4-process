<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use plibv4\process\Timeshare;
use plibv4\process\Task;
use plibv4\process\Scheduler;
use plibv4\process\TimeshareObserver;
use plibv4\process\TimeshareObservers;
class TimeshareObserversTest extends TestCase {
	public function testAddObserver(): void {
		$timeshare = new TimeshareObservers();
		$timeshare->addTimeshareObserver(new TrackObserver());
		
		$reflection = new ReflectionClass($timeshare);
		$name = $reflection->getProperty("timeshareObservers");
		$name->setAccessible(true);
		$this->assertSame(1, count($name->getValue($timeshare)));
	}

	public function testAddDuplicate(): void {
		$timeshare = new TimeshareObservers();
		$to = new TrackObserver();
		$timeshare->addTimeshareObserver($to);
		$timeshare->addTimeshareObserver($to);
	
		$reflection = new ReflectionClass($timeshare);
		$name = $reflection->getProperty("timeshareObservers");
		$name->setAccessible(true);
		$this->assertSame(1, count($name->getValue($timeshare)));
	}
	
	function testOnAdd(): void {
		$timeshare = new Timeshare();
		$to = new TrackObserver();

		$obs = new TimeshareObservers();
		$obs->addTimeshareObserver($to);
		$count = new Counter(15);
		
		$obs->onAdd($timeshare, $count);
		$to->onAddCalled($timeshare, $count, 1);
	}
	
	function testOnStart(): void {
		$timeshare = new Timeshare();
		$to = new TrackObserver();

		$obs = new TimeshareObservers();
		$obs->addTimeshareObserver($to);
		$count = new Counter(15);
		
		$obs->onStart($timeshare, $count);
		$to->onStartCalled($timeshare, $count, 1);
	}

	function testOnRemove(): void {
		$timeshare = new Timeshare();
		$to = new TrackObserver();

		$obs = new TimeshareObservers();
		$obs->addTimeshareObserver($to);
		$count = new Counter(15);
		
		$obs->onRemove($timeshare, $count, Scheduler::FINISH);
		$to->onRemoveCalled($timeshare, $count, Scheduler::FINISH, 1);
	}

	function testOnPause(): void {
		$timeshare = new Timeshare();
		$to = new TrackObserver();

		$obs = new TimeshareObservers();
		$obs->addTimeshareObserver($to);
		$count = new Counter(15);
		
		$obs->onPause($timeshare, $count);
		$to->onPauseCalled($timeshare, $count, 1);
	}

	function testOnResume(): void {
		$timeshare = new Timeshare();
		$to = new TrackObserver();

		$obs = new TimeshareObservers();
		$obs->addTimeshareObserver($to);
		$count = new Counter(15);
		
		$obs->onResume($timeshare, $count);
		$to->onResumeCalled($timeshare, $count, 1);
	}

	function testOnError(): void {
		$timeshare = new Timeshare();
		$to = new TrackObserver();

		$obs = new TimeshareObservers();
		$obs->addTimeshareObserver($to);
		$count = new Counter(15);
		$ex = new \Exception("test");
		
		$obs->onError($timeshare, $count, $ex, Scheduler::FINISH);
		$to->onErrorCalled($timeshare, $count, $ex, Scheduler::FINISH, 1);
	}

}