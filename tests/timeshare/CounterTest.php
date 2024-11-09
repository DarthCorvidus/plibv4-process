<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use plibv4\process\Timeshare;
class CounterTest extends TestCase {
	function testCounter() {
		$timeshare = new Timeshare();
		$counter = new Counter(500);
		while($counter->__tsLoop($timeshare)) {
			
		}
		$this->assertSame(500, $counter->getCount());
		$this->assertSame(0, $counter->terminated);
	}
	
	function testStart() {
		$timeshare = new Timeshare();
		$counter = new Counter(500);
		$this->assertSame(0, $counter->started);
		$counter->__tsStart($timeshare);
		$this->assertSame(1, $counter->started);
	}

	function testFinish() {
		$timeshare = new Timeshare();
		$counter = new Counter(500);
		$this->assertSame(0, $counter->finished);
		$counter->__tsFinish($timeshare);
		$this->assertSame(1, $counter->finished);
	}
	
	function testTerminate() {
		$timeshare = new Timeshare();
		$counter = new Counter(500);
		$i = 0;
		while($counter->__tsLoop($timeshare)) {
			$i++;
			if($i == 245 && $counter->__tsTerminate($timeshare)) {
				break;
			}
		}
		$this->assertSame(245, $counter->getCount());
		$this->assertSame(1, $counter->terminated);
	}
	
	function testTerminateModulo() {
		$timeshare = new Timeshare();
		$counter = new Counter(500, 100);
		$i = 0;
		while($counter->__tsLoop($timeshare)) {
			$i++;
			if($i >= 245 && $counter->__tsTerminate($timeshare)) {
				break;
			}
		}
		$this->assertSame(300, $counter->getCount());
		// Terminate is called 56 before 300 is reached and it returns false.
		$this->assertSame(56, $counter->terminated);
	}
	/**
	 * Edge case: the max value has precedence over the next mod 100 value.
	 */
	function testTerminateEdgeCase() {
		$timeshare = new Timeshare();
		$counter = new Counter(250, 100);
		$i = 0;
		while($counter->__tsLoop($timeshare)) {
			$i++;
			if($i >= 245) {
				$counter->__tsTerminate($timeshare);
			}
		}
		$this->assertSame(250, $counter->getCount());
		// Terminate is called 5 times before it returns false
		$this->assertSame(5, $counter->terminated);
	}

	function testExceptionLoop() {
		$timeshare = new Timeshare();
		$counter = new Counter(250, 100);
		$counter->exceptionOn(10);
		for($i = 0; $i < 9;$i++) {
			$counter->__tsLoop($timeshare);
		}
		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage("This exception is an expection.");
		$counter->__tsLoop($timeshare);
	}
	
	function testExceptionStart() {
		$timeshare = new Timeshare();
		$counter = new Counter(250, 100);
		$counter->exceptionStart = true;
		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage("exception at start");
		$counter->__tsStart($timeshare);
	}

	function testExceptionFinish() {
		$timeshare = new Timeshare();
		$counter = new Counter(250, 100);
		$counter->exceptionFinish = true;
		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage("exception at finish.");
		$counter->__tsFinish($timeshare);
	}

	function testExceptionPause() {
		$timeshare = new Timeshare();
		$counter = new Counter(250, 100);
		$counter->exceptionPause = true;
		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage("exception at pause.");
		$counter->__tsPause($timeshare);
	}
	
	function testExceptionResume() {
		$timeshare = new Timeshare();
		$counter = new Counter(250, 100);
		$counter->exceptionResume = true;
		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage("exception at resume.");
		$counter->__tsResume($timeshare);
	}
}
