<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use plibv4\process\Timeshare;
class TrackObserverTest extends TestCase {
	function testOnAdd() {
		$to = new TrackObserver();
		$timeshare = new Timeshare();
		$count01 = new Counter(25);
		$count02 = new Counter(30);
		$to->onAdd($timeshare, $count01);
		$this->assertSame(1, $to->countAdded);
		$this->assertSame(0, $to->countRemoved);
		$this->assertSame(0, $to->countError);
		$this->assertSame(0, $to->countStarted);
		$this->assertSame(0, $to->countPaused);
		$this->assertSame(0, $to->countResumed);
		$this->assertSame(0, $to->lastStep);
		$this->assertSame(null, $to->lastTaskError);
		$this->assertSame(null, $to->lastTaskRemoved);
		$this->assertSame($count01, $to->lastTaskAdded);
		$this->assertSame(null, $to->lastTaskStarted);
		$this->assertSame(null, $to->lastTaskPaused);
		$this->assertSame(null, $to->lastTaskResumed);
		$this->assertSame(null, $to->lastException);	

		$to->onAdd($timeshare, $count02);
		$this->assertSame(2, $to->countAdded);
		$this->assertSame(0, $to->countRemoved);
		$this->assertSame(0, $to->countError);
		$this->assertSame(0, $to->countStarted);
		$this->assertSame(0, $to->countPaused);
		$this->assertSame(0, $to->countResumed);
		$this->assertSame(0, $to->lastStep);
		$this->assertSame(null, $to->lastTaskError);
		$this->assertSame(null, $to->lastTaskRemoved);
		$this->assertSame($count02, $to->lastTaskAdded);
		$this->assertSame(null, $to->lastTaskStarted);
		$this->assertSame(null, $to->lastTaskPaused);
		$this->assertSame(null, $to->lastTaskResumed);
		$this->assertSame(null, $to->lastException);
	}
	
	function testOnRemove() {
		$to = new TrackObserver();
		$timeshare = new Timeshare();
		$count01 = new Counter(25);
		$count02 = new Counter(30);
		$to->onRemove($timeshare, $count01, Timeshare::FINISH);
		$this->assertSame(0, $to->countAdded);
		$this->assertSame(1, $to->countRemoved);
		$this->assertSame(0, $to->countError);
		$this->assertSame(0, $to->countStarted);
		$this->assertSame(0, $to->countPaused);
		$this->assertSame(0, $to->countResumed);
		$this->assertSame(Timeshare::FINISH, $to->lastStep);
		$this->assertSame(null, $to->lastTaskError);
		$this->assertSame($count01, $to->lastTaskRemoved);
		$this->assertSame(null, $to->lastTaskAdded);
		$this->assertSame(null, $to->lastTaskStarted);
		$this->assertSame(null, $to->lastTaskPaused);
		$this->assertSame(null, $to->lastTaskResumed);
		$this->assertSame(null, $to->lastException);
		
		$to->onRemove($timeshare, $count02, Timeshare::ERROR);
		$this->assertSame(0, $to->countAdded);
		$this->assertSame(2, $to->countRemoved);
		$this->assertSame(0, $to->countError);
		$this->assertSame(0, $to->countStarted);
		$this->assertSame(0, $to->countPaused);
		$this->assertSame(0, $to->countResumed);
		$this->assertSame(Timeshare::ERROR, $to->lastStep);
		$this->assertSame(null, $to->lastTaskError);
		$this->assertSame($count02, $to->lastTaskRemoved);
		$this->assertSame(null, $to->lastTaskAdded);
		$this->assertSame(null, $to->lastTaskStarted);
		$this->assertSame(null, $to->lastTaskPaused);
		$this->assertSame(null, $to->lastTaskResumed);
		$this->assertSame(null, $to->lastException);
	}
	
	function testOnError() {
		$to = new TrackObserver();
		$timeshare = new Timeshare();
		$count01 = new Counter(25);
		$count02 = new Counter(30);
		$ex01 = new \RuntimeException();
		$ex02 = new \RuntimeException();
		$to->onError($timeshare, $count01, $ex01, Timeshare::FINISH);
		$this->assertSame(0, $to->countAdded);
		$this->assertSame(0, $to->countRemoved);
		$this->assertSame(1, $to->countError);
		$this->assertSame(0, $to->countStarted);
		$this->assertSame(0, $to->countPaused);
		$this->assertSame(0, $to->countResumed);
		$this->assertSame(Timeshare::FINISH, $to->lastStep);
		$this->assertSame($count01, $to->lastTaskError);
		$this->assertSame(null, $to->lastTaskRemoved);
		$this->assertSame(null, $to->lastTaskAdded);
		$this->assertSame(null, $to->lastTaskStarted);
		$this->assertSame(null, $to->lastTaskPaused);
		$this->assertSame(null, $to->lastTaskResumed);
		$this->assertSame($ex01, $to->lastException);
		
		$to->onError($timeshare, $count02, $ex02, Timeshare::START);
		$this->assertSame(0, $to->countAdded);
		$this->assertSame(0, $to->countRemoved);
		$this->assertSame(2, $to->countError);
		$this->assertSame(0, $to->countStarted);
		$this->assertSame(0, $to->countPaused);
		$this->assertSame(0, $to->countResumed);
		$this->assertSame(Timeshare::START, $to->lastStep);
		$this->assertSame($count02, $to->lastTaskError);
		$this->assertSame(null, $to->lastTaskRemoved);
		$this->assertSame(null, $to->lastTaskAdded);
		$this->assertSame(null, $to->lastTaskStarted);
		$this->assertSame(null, $to->lastTaskPaused);
		$this->assertSame(null, $to->lastTaskResumed);
		$this->assertSame($ex02, $to->lastException);
	}
	
	function testOnStart() {
		$to = new TrackObserver();
		$timeshare = new Timeshare();
		$count01 = new Counter(25);
		$count02 = new Counter(30);
		$to->onStart($timeshare, $count01);
		$this->assertSame(0, $to->countAdded);
		$this->assertSame(0, $to->countRemoved);
		$this->assertSame(0, $to->countError);
		$this->assertSame(1, $to->countStarted);
		$this->assertSame(0, $to->countPaused);
		$this->assertSame(0, $to->countResumed);
 		$this->assertSame(0, $to->lastStep);
		$this->assertSame(null, $to->lastTaskError);
		$this->assertSame(null, $to->lastTaskRemoved);
		$this->assertSame(null, $to->lastTaskAdded);
		$this->assertSame($count01, $to->lastTaskStarted);
		$this->assertSame(null, $to->lastTaskPaused);
		$this->assertSame(null, $to->lastTaskResumed);
		$this->assertSame(null, $to->lastException);
		
		$to->onStart($timeshare, $count02);
		$this->assertSame(0, $to->countAdded);
		$this->assertSame(0, $to->countRemoved);
		$this->assertSame(0, $to->countError);
		$this->assertSame(2, $to->countStarted);
		$this->assertSame(0, $to->countPaused);
		$this->assertSame(0, $to->countResumed);
 		$this->assertSame(0, $to->lastStep);
		$this->assertSame(null, $to->lastTaskError);
		$this->assertSame(null, $to->lastTaskRemoved);
		$this->assertSame(null, $to->lastTaskAdded);
		$this->assertSame($count02, $to->lastTaskStarted);
		$this->assertSame(null, $to->lastTaskPaused);
		$this->assertSame(null, $to->lastTaskResumed);
		$this->assertSame(null, $to->lastException);
	}

	function testOnPause() {
		$to = new TrackObserver();
		$timeshare = new Timeshare();
		$count01 = new Counter(25);
		$count02 = new Counter(30);
		$to->onPause($timeshare, $count01);
		$this->assertSame(0, $to->countAdded);
		$this->assertSame(0, $to->countRemoved);
		$this->assertSame(0, $to->countError);
		$this->assertSame(0, $to->countStarted);
		$this->assertSame(1, $to->countPaused);
		$this->assertSame(0, $to->countResumed);
 		$this->assertSame(0, $to->lastStep);
		$this->assertSame(null, $to->lastTaskError);
		$this->assertSame(null, $to->lastTaskRemoved);
		$this->assertSame(null, $to->lastTaskAdded);
		$this->assertSame(null, $to->lastTaskStarted);
		$this->assertSame($count01, $to->lastTaskPaused);
		$this->assertSame(null, $to->lastTaskResumed);
		$this->assertSame(null, $to->lastException);
		
		$to->onPause($timeshare, $count02);
		$this->assertSame(0, $to->countAdded);
		$this->assertSame(0, $to->countRemoved);
		$this->assertSame(0, $to->countError);
		$this->assertSame(0, $to->countStarted);
		$this->assertSame(2, $to->countPaused);
		$this->assertSame(0, $to->countResumed);
 		$this->assertSame(0, $to->lastStep);
		$this->assertSame(null, $to->lastTaskError);
		$this->assertSame(null, $to->lastTaskRemoved);
		$this->assertSame(null, $to->lastTaskAdded);
		$this->assertSame(null, $to->lastTaskStarted);
		$this->assertSame($count02, $to->lastTaskPaused);
		$this->assertSame(null, $to->lastTaskResumed);
		$this->assertSame(null, $to->lastException);
	}

	function testOnResume() {
		$to = new TrackObserver();
		$timeshare = new Timeshare();
		$count01 = new Counter(25);
		$count02 = new Counter(30);
		$to->onResume($timeshare, $count01);
		$this->assertSame(0, $to->countAdded);
		$this->assertSame(0, $to->countRemoved);
		$this->assertSame(0, $to->countError);
		$this->assertSame(0, $to->countStarted);
		$this->assertSame(0, $to->countPaused);
		$this->assertSame(1, $to->countResumed);
 		$this->assertSame(0, $to->lastStep);
		$this->assertSame(null, $to->lastTaskError);
		$this->assertSame(null, $to->lastTaskRemoved);
		$this->assertSame(null, $to->lastTaskAdded);
		$this->assertSame(null, $to->lastTaskStarted);
		$this->assertSame(null, $to->lastTaskPaused);
		$this->assertSame($count01, $to->lastTaskResumed);
		$this->assertSame(null, $to->lastException);
		
		$to->onResume($timeshare, $count02);
		$this->assertSame(0, $to->countAdded);
		$this->assertSame(0, $to->countRemoved);
		$this->assertSame(0, $to->countError);
		$this->assertSame(0, $to->countStarted);
		$this->assertSame(0, $to->countPaused);
		$this->assertSame(2, $to->countResumed);
 		$this->assertSame(0, $to->lastStep);
		$this->assertSame(null, $to->lastTaskError);
		$this->assertSame(null, $to->lastTaskRemoved);
		$this->assertSame(null, $to->lastTaskAdded);
		$this->assertSame(null, $to->lastTaskStarted);
		$this->assertSame(null, $to->lastTaskPaused);
		$this->assertSame($count02, $to->lastTaskResumed);
		$this->assertSame(null, $to->lastException);
	}

}