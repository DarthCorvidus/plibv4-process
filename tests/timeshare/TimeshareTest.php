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
}
