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
		$timeshare->loop();
		$this->assertSame(1, $count01->getCount());
		$this->assertSame(1, $count01->started);
		$this->assertSame(0, $count02->getCount());
		$this->assertSame(0, $count02->started);
		$timeshare->loop();
		$this->assertSame(1, $count01->getCount());
		$this->assertSame(1, $count01->started);
		$this->assertSame(1, $count02->getCount());
		$timeshare->loop();
		$this->assertSame(2, $count01->getCount());
		$this->assertSame(1, $count02->getCount());
		$this->assertSame(1, $count01->started);
		$this->assertSame(1, $count01->started);
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
	}
	
	function testTerminate() {
		$timeshare = new plibv4\process\Timeshare();
		$count01 = new Counter(500);
		$count02 = new Counter(1000);
		$timeshare->addTimeshared($count01);
		$timeshare->addTimeshared($count02);
		$i = 0;
		while($timeshare->loop()) {
			$i++;
			if($i == 94) {
				$timeshare->terminate();
			}
		}
		$this->assertSame(47, $count01->getCount());
		$this->assertSame(47, $count02->getCount());
	}

	function testDeferredTerminate() {
		$timeshare = new plibv4\process\Timeshare();
		$count01 = new Counter(500);
		$count02 = new Counter(1000, 100);
		$timeshare->addTimeshared($count01);
		$timeshare->addTimeshared($count02);
		$i = 0;
		while($timeshare->loop()) {
			$i++;
			if($i >= 94 and $timeshare->terminate()) {
				break;
			}
		}
		$this->assertSame(47, $count01->getCount());
		$this->assertSame(100, $count02->getCount());
	}

}
