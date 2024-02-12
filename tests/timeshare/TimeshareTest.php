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
}
