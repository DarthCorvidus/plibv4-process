<?php
declare(strict_types=1);
namespace plibv4\process;
use PHPUnit\Framework\TestCase;
use Exception;
final class TimeshareObserversTest extends TestCase {
	public function testAddObserver(): void {
		$timeshare = new TimeshareObservers();
		$timeshare->addTimeshareObserver(new TrackObserver());
		
		$this->assertSame(1, $timeshare->getCount());
	}

	public function testAddDuplicate(): void {
		$timeshare = new TimeshareObservers();
		$to = new TrackObserver();
		$timeshare->addTimeshareObserver($to);
		$timeshare->addTimeshareObserver($to);
	
		$this->assertSame(1, $timeshare->getCount());
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
		$ex = new Exception("test");
		
		$obs->onError($timeshare, $count, $ex, Scheduler::FINISH);
		$to->onErrorCalled($timeshare, $count, $ex, Scheduler::FINISH, 1);
	}

}