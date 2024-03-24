<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use \plibv4\process\Timeshare;
use \plibv4\process\Task;
use \plibv4\process\TimeshareObserver;
class TimeshareObserverTest extends TestCase {
	private int $addCount = 0;
	private int $lastStatus = 0;
	private int $removeCount = 0;
	private int $startCount = 0;
	private int $errorCount = 0;
	private int $lastErrorStatus = 0;
	private ?Task $lastAdded = null;
	private ?Task $lastRemoved = null;
	private ?Task $lastStarted = null;
	private ?Task $lastError = null;
	private ?\Exception $lastException = null;

	public function testOnAdd() {
		$timeshare = new Timeshare();
		$to = new TrackObserver();
		$timeshare->addTimeshareObserver($to);
		$count01 = new Counter(15);
		$count02 = new Counter(20);
		
		$timeshare->addTask($count01);
		$to->onAddCalled($timeshare, $count01, 1);
		
		$timeshare->addTask($count02);
		$to->onAddCalled($timeshare, $count02, 2);
	}

	public function testOnStart() {
		$timeshare = new Timeshare();
		$to = new TrackObserver();
		$timeshare->addTimeshareObserver($to);
		$count01 = new Counter(15);
		$count02 = new Counter(20);
		$count03 = new Counter(10);
		$timeshare->addTask($count01);
		$timeshare->addTask($count02);

		$timeshare->__tsLoop();
		$to->onStartCalled($timeshare, $count01, 1);

		$timeshare->__tsLoop();
		$to->onStartCalled($timeshare, $count02, 2);
		$timeshare->run();

		$timeshare->addTask($count03);
		$timeshare->run();
		$to->onStartCalled($timeshare, $count03, 3);
	}
	

	public function testOnRemoveFinished() {
		$timeshare = new Timeshare();
		$to = new TrackObserver();
		$timeshare->addTimeshareObserver($to);
		$count01 = new Counter(15);
		$count02 = new Counter(20);
		
		$timeshare->addTask($count01);
		$timeshare->addTask($count02);
		$timeshare->run();
		
		$to->onRemoveCalled($timeshare, $count02, Timeshare::FINISH, 2);
	}
	
	public function testOnRemoveTerminated() {
		$timeshare = new Timeshare();
		$to = new TrackObserver();
		$timeshare->addTimeshareObserver($to);
		$count01 = new Counter(15);
		$count02 = new Counter(20);
		
		$timeshare->addTask($count01);
		$timeshare->addTask($count02);
		$i = 0;
		while($timeshare->__tsLoop()) {
			if($i == 10) {
				$timeshare->__tsTerminate();
			}
			$i++;
		}
		$to->onRemoveCalled($timeshare, $count01, Timeshare::TERMINATE, 2);
	}

	public function testOnErrorStart() {
		$timeshare = new Timeshare();
		$to = new TrackObserver();
		$timeshare->addTimeshareObserver($to);
		$count01 = new Counter(15);
		$count01->exceptionStart = true;
		$count02 = new Counter(20);
		$count02->exceptionStart = true;
		
		$timeshare->addTask($count01);
		$timeshare->addTask($count02);
		$timeshare->__tsLoop();
		$to->onErrorCalled($timeshare, $count01, $count01->exceptionReceived, Timeshare::START, 1);
		$this->assertSame(0, $count01->getCount());

		$timeshare->run();
		$to->onErrorCalled($timeshare, $count02, $count02->exceptionReceived, Timeshare::START, 2);
		$this->assertSame(0, $count02->getCount());
	}

	public function testOnErrorLoop() {
		$timeshare = new Timeshare();
		$to = new TrackObserver();
		$timeshare->addTimeshareObserver($to);
		$count01 = new Counter(15);
		$count01->exceptionOn(10);
		$count02 = new Counter(20);
		$count02->exceptionOn(13);
		$timeshare->addTask($count01);
		$timeshare->addTask($count02);
		// 11 iterations to trigger the first error
		for($i = 0; $i <= 10;$i++) {
			$timeshare->__tsLoop();
			$timeshare->__tsLoop();
		}
		$to->onErrorCalled($timeshare, $count01, $count01->exceptionReceived, Timeshare::LOOP, 1);

		$timeshare->run();
		$to->onErrorCalled($timeshare, $count02, $count02->exceptionReceived, Timeshare::LOOP, 2);
		
		$this->assertSame(10, $count01->getCount());
		$this->assertSame(13, $count02->getCount());

	}
	
	public function testOnErrorFinish() {
		$timeshare = new Timeshare();
		$to = new TrackObserver();
		$timeshare->addTimeshareObserver($to);
		$count01 = new Counter(15);
		$count01->exceptionFinish = true;
		$count02 = new Counter(20);
		$count02->exceptionFinish = true;
		$timeshare->addTask($count01);
		$timeshare->addTask($count02);
		// 11 iterations to trigger the first error
		for($i = 0; $i <= 15;$i++) {
			$timeshare->__tsLoop();
			$timeshare->__tsLoop();
		}
		$to->onErrorCalled($timeshare, $count01, $count01->exceptionReceived, Timeshare::FINISH, 1);
		$this->assertSame(15, $count01->getCount());

		$timeshare->run();
		
		$to->onErrorCalled($timeshare, $count02, $count02->exceptionReceived, Timeshare::FINISH, 2);
		$this->assertSame(15, $count01->getCount());
		$this->assertSame(20, $count02->getCount());
	}	
}