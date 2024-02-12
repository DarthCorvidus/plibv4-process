<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;
class TimeshareTest extends TestCase {
	function testConstruct() {
		$construct = new plibv4\Timeshare\Timeshare();
		$this->assertInstanceOf(\plibv4\Timeshare\Timeshare::class, $construct);
	}
	
	function testGetCountZero() {
		$timeshare = new plibv4\Timeshare\Timeshare();
		$this->assertEquals(0, $timeshare->getProcessCount());
	}
	
	function testGetProcessCountAdded() {
		$timeshare = new plibv4\Timeshare\Timeshare();
		$count = new Counter(500);
		$timeshare->addTimeshared($count);
		$this->assertEquals(1, $timeshare->getProcessCount());
	}
	
	/**
	 * On each loop, one of the tasks is called in the order added to Timeshare.
	 */
	function testLoop() {
		$timeshare = new plibv4\Timeshare\Timeshare();
		$count01 = new Counter(500);
		$count02 = new Counter(1000, 100);
		$timeshare->addTimeshared($count01);
		$timeshare->addTimeshared($count02);
		$timeshare->loop();
		$this->assertEquals(1, $count01->getCount());
		$this->assertEquals(0, $count02->getCount());
		$timeshare->loop();
		$this->assertEquals(1, $count01->getCount());
		$this->assertEquals(1, $count02->getCount());
		$timeshare->loop();
		$this->assertEquals(2, $count01->getCount());
		$this->assertEquals(1, $count02->getCount());
	}
	
	/**
	 * Run executes until loop() of every Timeshared instance returns false.
	 */
	function testRun() {
		$timeshare = new plibv4\Timeshare\Timeshare();
		$count01 = new Counter(500);
		$count02 = new Counter(1000);
		$timeshare->addTimeshared($count01);
		$timeshare->addTimeshared($count02);
		$timeshare->run();
		$this->assertEquals(500, $count01->getCount());
		$this->assertEquals(1000, $count02->getCount());
	}
	
	function testTerminate() {
		$timeshare = new plibv4\Timeshare\Timeshare();
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
		$this->assertEquals(47, $count01->getCount());
		$this->assertEquals(47, $count02->getCount());
	}

	function testDeferredTerminate() {
		$timeshare = new plibv4\Timeshare\Timeshare();
		$count01 = new Counter(500);
		$count02 = new Counter(1000, 100);
		$timeshare->addTimeshared($count01);
		$timeshare->addTimeshared($count02);
		$i = 0;
		while($timeshare->loop()) {
			$i++;
			if($i == 94) {
				$timeshare->terminate();
			}
		}
		$this->assertEquals(47, $count01->getCount());
		$this->assertEquals(100, $count02->getCount());
	}

}
