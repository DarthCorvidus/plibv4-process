<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;
class TimeshareTest extends TestCase {
	function testConstruct() {
		$construct = new plibv4\process\Timeshare();
		$this->assertInstanceOf(\plibv4\process\Timeshare::class, $construct);
	}
	
	function testGetCountZero() {
		$timeshare = new plibv4\process\Timeshare();
		$this->assertSame(0, $timeshare->getProcessCount());
	}
	
	function testGetProcessCountAdded() {
		$timeshare = new plibv4\process\Timeshare();
		$count = new Counter(500);
		$timeshare->addTimeshared($count);
		$this->assertSame(1, $timeshare->getProcessCount());
		$this->assertSame(0, $count->started);
		$this->assertSame(0, $count->finished);
		$this->assertSame(0, $count->terminated);
	}
	
	/**
	 * On each loop, one of the tasks is called in the order added to Timeshare.
	 * Make sure that Timeshared::start is only called one time.
	 */
	function testLoop() {
		$timeshare = new plibv4\process\Timeshare();
		$count01 = new Counter(500);
		$count02 = new Counter(1000, 100);
		$timeshare->addTimeshared($count01);
		$timeshare->addTimeshared($count02);
		$this->assertSame(0, $count01->started);
		$this->assertSame(0, $count02->started);
		$timeshare->__tsLoop();
		$this->assertSame(1, $count01->getCount());
		$this->assertSame(1, $count01->started);
		$this->assertSame(0, $count02->getCount());
		$this->assertSame(0, $count02->started);
		$timeshare->__tsLoop();
		$this->assertSame(1, $count01->getCount());
		$this->assertSame(1, $count01->started);
		$this->assertSame(1, $count02->getCount());
		$timeshare->__tsLoop();
		$this->assertSame(2, $count01->getCount());
		$this->assertSame(1, $count02->getCount());
		
		$this->assertSame(1, $count01->started);
		$this->assertSame(1, $count02->started);
	}
	
	/**
	 * Run executes until loop() of every Timeshared instance returns false.
	 */
	function testRun() {
		$timeshare = new plibv4\process\Timeshare();
		$count01 = new Counter(500);
		$count02 = new Counter(1000);
		$timeshare->addTimeshared($count01);
		$timeshare->addTimeshared($count02);
		$timeshare->run();
		$this->assertSame(500, $count01->getCount());
		$this->assertSame(1000, $count02->getCount());
		
		$this->assertSame(1, $count01->started);
		$this->assertSame(1, $count02->started);

		$this->assertSame(1, $count01->finished);
		$this->assertSame(1, $count02->finished);

		$this->assertSame(0, $count01->terminated);
		$this->assertSame(0, $count02->terminated);

	}
	
	function testTerminate() {
		$timeshare = new plibv4\process\Timeshare();
		$count01 = new Counter(500);
		$count02 = new Counter(1000);
		$timeshare->addTimeshared($count01);
		$timeshare->addTimeshared($count02);
		$i = 0;
		while($timeshare->__tsLoop()) {
			$i++;
			if($i == 94) {
				$timeshare->__tsTerminate();
			}
		}
		$this->assertSame(47, $count01->getCount());
		$this->assertSame(47, $count02->getCount());
		
		$this->assertSame(1, $count01->started);
		$this->assertSame(1, $count02->started);

		$this->assertSame(0, $count01->finished);
		$this->assertSame(0, $count02->finished);

		$this->assertSame(1, $count01->terminated);
		$this->assertSame(1, $count02->terminated);

	}

	function testDeferredTerminate() {
		$timeshare = new plibv4\process\Timeshare();
		$count01 = new Counter(500);
		$count02 = new Counter(1000, 100);
		$timeshare->addTimeshared($count01);
		$timeshare->addTimeshared($count02);
		$i = 0;
		while($timeshare->__tsLoop()) {
			$i++;
			if($i == 94) {
				$timeshare->__tsTerminate();
			}
		}
		$this->assertSame(47, $count01->getCount());
		$this->assertSame(100, $count02->getCount());
		
		$this->assertSame(1, $count01->started);
		$this->assertSame(1, $count02->started);

		$this->assertSame(0, $count01->finished);
		$this->assertSame(0, $count02->finished);

		$this->assertSame(1, $count01->terminated);
		// Counter::terminate was deferred 54 times
		$this->assertSame(54, $count02->terminated);
	}

	function testTimeoutSeconds() {
		$timeshare = new plibv4\process\Timeshare();
		$timeshare->addTimeshared(new Stubborn());
		$timeshare->setTimeout(1, 0);
		$i = 0;
		$started = microtime(true)*1000000;
		while($timeshare->__tsLoop()) {
			$timeshare->__tsTerminate();
		}
		$passed = microtime(true)*1000000 - $started;
		$this->assertSame(true, $passed >= 0.9*1000000);
		$this->assertSame(true, $passed <= 1.1*1000000);
	}
	
	function testTimeoutMicroeconds() {
		$timeshare = new plibv4\process\Timeshare();
		$timeshare->addTimeshared(new Stubborn());
		$timeshare->setTimeout(0, 500000);
		$i = 0;
		$started = microtime(true)*1000000;
		while($timeshare->__tsLoop()) {
			$timeshare->__tsTerminate();
		}
		$passed = microtime(true)*1000000 - $started;
		$this->assertSame(true, $passed >= 0.4*1000000);
		$this->assertSame(true, $passed <= 0.6*1000000);
	}
	
	function testErrorStart() {
		$timeshare = new plibv4\process\Timeshare();
		$count = new Counter(10);
		$count->exceptionStart = true;
		$timeshare->addTimeshared($count);
		$timeshare->run();
		$this->assertSame(1, $count->exceptionThrown);
		$this->assertSame(plibv4\process\TimeshareObserver::START, $count->exceptionStep);
		$this->assertSame("exception at start", $count->exceptionReceived->getMessage());
		$this->assertSame(0, $count->finished);
	}
	
	function testErrorLoop() {
		$timeshare = new plibv4\process\Timeshare();
		$count = new Counter(100);
		$count->exceptionOn(10);
		$timeshare->addTimeshared($count);
		$timeshare->run();
		$this->assertSame(1, $count->exceptionThrown);
		$this->assertSame(plibv4\process\TimeshareObserver::LOOP, $count->exceptionStep);
		$this->assertSame("This exception is an expection.", $count->exceptionReceived->getMessage());
		$this->assertSame(0, $count->finished);
	}
}
