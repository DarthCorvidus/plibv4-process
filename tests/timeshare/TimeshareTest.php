<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use plibv4\process\Timeshare;
class TimeshareTest extends TestCase {
	function testConstruct() {
		$construct = new plibv4\process\Timeshare();
		$this->assertInstanceOf(\plibv4\process\Timeshare::class, $construct);
	}
	
	function testGetCountZero() {
		$timeshare = new plibv4\process\Timeshare();
		$this->assertSame(0, $timeshare->getProcessCount());
	}
	
	function testGetTimeoutSeconds() {
		$timeshare = new Timeshare();
		$timeshare->setTimeout(20, 0);
		$this->assertSame(20000000, $timeshare->getTimeout());
	}

	function testGetTimeoutMicroseconds() {
		$timeshare = new Timeshare();
		$timeshare->setTimeout(0, 150);
		$this->assertSame(150, $timeshare->getTimeout());
	}

	function testGetTimeoutBoth() {
		$timeshare = new Timeshare();
		$timeshare->setTimeout(20, 150);
		$this->assertSame(20000150, $timeshare->getTimeout());
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
	
	function testStartProcess() {
		$timeshare = new plibv4\process\Timeshare();
		$count01 = new Counter(500);
		$count02 = new Counter(500);
		$timeshare->addTimeshared($count01);
		$timeshare->addTimeshared($count02);
		$timeshare->__tsLoop();
		$this->assertSame(2, $timeshare->getProcessCount());
		$this->assertSame(1, $count01->started);
		$this->assertSame(0, $count01->finished);
		$this->assertSame(0, $count01->terminated);
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
		/**
		 * Calling $count01->__tsStart() & $count01->__tsLoop()
		 */
		$timeshare->__tsLoop();
		$this->assertSame(0, $count01->getCount());
		$this->assertSame(1, $count01->started);
		$this->assertSame(0, $count02->getCount());
		$this->assertSame(0, $count02->started);
		/**
		 * Calling $count02->__tsStart() & $count02->__tsLoop()
		 */
		$timeshare->__tsLoop();
		$this->assertSame(0, $count01->getCount());
		$this->assertSame(1, $count01->started);
		$this->assertSame(0, $count02->getCount());
		$this->assertSame(1, $count02->started);
		/**
		 * Calling $count01->__tsLoop()
		 */
		$timeshare->__tsLoop();
		$this->assertSame(1, $count01->getCount());
		$this->assertSame(1, $count01->started);
		$this->assertSame(0, $count02->getCount());
		$this->assertSame(1, $count02->started);
		$timeshare->__tsLoop();
		$this->assertSame(1, $count01->getCount());
		$this->assertSame(1, $count01->started);
		$this->assertSame(1, $count02->getCount());
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
	
	function testTerminateAll() {
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
		$this->assertSame(46, $count01->getCount());
		$this->assertSame(46, $count02->getCount());
		
		$this->assertSame(1, $count01->started);
		$this->assertSame(1, $count02->started);

		$this->assertSame(0, $count01->finished);
		$this->assertSame(0, $count02->finished);

		$this->assertSame(1, $count01->terminated);
		$this->assertSame(1, $count02->terminated);

	}

	function testDeferredTerminateAll() {
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
		$this->assertSame(46, $count01->getCount());
		$this->assertSame(100, $count02->getCount());
		
		$this->assertSame(1, $count01->started);
		$this->assertSame(1, $count02->started);

		$this->assertSame(0, $count01->finished);
		$this->assertSame(0, $count02->finished);

		$this->assertSame(1, $count01->terminated);
		// Counter::terminate was deferred 54 times
		$this->assertSame(55, $count02->terminated);
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
		$this->assertSame(plibv4\process\Timeshare::START, $count->exceptionStep);
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
		$this->assertSame(\plibv4\process\Timeshare::LOOP, $count->exceptionStep);
		$this->assertSame("This exception is an expection.", $count->exceptionReceived->getMessage());
		$this->assertSame(0, $count->finished);
	}

	function testErrorFinish() {
		$timeshare = new plibv4\process\Timeshare();
		$count = new Counter(100);
		$count->exceptionFinish = true;
		$timeshare->addTimeshared($count);
		$timeshare->run();
		$this->assertSame(1, $count->exceptionThrown);
		$this->assertSame(\plibv4\process\Timeshare::FINISH, $count->exceptionStep);
		$this->assertSame("exception at finish.", $count->exceptionReceived->getMessage());
		$this->assertSame(0, $count->finished);
	}
	
	function testHasTimeshared() {
		$timeshare = new Timeshare();
		$count01 = new Counter(500);
		$count02 = new Counter(1000);
		$count03 = new Counter(1000);
		$count04 = new Counter(1500);
		$timeshare->addTimeshared($count01);
		$timeshare->addTimeshared($count02);
		$this->assertSame(true, $timeshare->hasTimeshared($count01));
		$this->assertSame(true, $timeshare->hasTimeshared($count02));
		$this->assertSame(false, $timeshare->hasTimeshared($count03));
		$this->assertSame(false, $timeshare->hasTimeshared($count04));
	}
	
	function testTerminate() {
		$timeshare = new Timeshare();
		$count01 = new Counter(500);
		$count02 = new Counter(1000);
		$timeshare->addTimeshared($count01);
		$timeshare->addTimeshared($count02);
		$i = 0;
		while($timeshare->__tsLoop()) {
			$i++;
			if($timeshare->hasTimeshared($count01) &&  $count01->getCount()==47) {
				$timeshare->terminate($count01);
			}
		}
		$this->assertSame(47, $count01->getCount());
		$this->assertSame(1000, $count02->getCount());
	}

	function testDeferredTerminate() {
		$timeshare = new Timeshare();
		$count01 = new Counter(500, 50);
		$count02 = new Counter(1000);
		$timeshare->addTimeshared($count01);
		$timeshare->addTimeshared($count02);
		while($timeshare->__tsLoop()) {
			if($timeshare->hasTimeshared($count01) &&  $count01->getCount()==47) {
				$timeshare->terminate($count01);
			}
		}
		$this->assertSame(50, $count01->getCount());
		$this->assertSame(1000, $count02->getCount());
	}
	
	function testKill() {
		$timeshare = new Timeshare();
		$count01 = new Counter(500, 50);
		$count02 = new Counter(1000);
		$timeshare->addTimeshared($count01);
		$timeshare->addTimeshared($count02);
		while($timeshare->__tsLoop()) {
			if($timeshare->hasTimeshared($count01) && $count01->getCount()==47) {
				$timeshare->kill($count01);
			}
		}
		$this->assertSame(47, $count01->getCount());
		$this->assertSame(1000, $count02->getCount());
	}

	function testKillUnavailableTask() {
		$timeshare = new Timeshare();
		$count01 = new Counter(500, 50);
		$count02 = new Counter(1000);
		$count03 = new Counter(1000);
		$timeshare->addTimeshared($count01);
		$timeshare->addTimeshared($count02);
		$timeshare->__tsLoop();
		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage("Task 'Counter' not found in Scheduler 'plibv4\process\Timeshare'");
		$timeshare->kill($count03);
	}
}
