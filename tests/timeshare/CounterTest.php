<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;
class CounterTest extends TestCase {
	function testCounter() {
		$counter = new Counter(500);
		while($counter->__tsLoop()) {
			
		}
		$this->assertSame(500, $counter->getCount());
		$this->assertSame(0, $counter->terminated);
	}
	
	function testStart() {
		$counter = new Counter(500);
		$this->assertSame(0, $counter->started);
		$counter->__tsStart();
		$this->assertSame(1, $counter->started);
	}

	function testFinish() {
		$counter = new Counter(500);
		$this->assertSame(0, $counter->finished);
		$counter->__tsFinish();
		$this->assertSame(1, $counter->finished);
	}
	
	function testTerminate() {
		$counter = new Counter(500);
		$i = 0;
		while($counter->__tsLoop()) {
			$i++;
			if($i == 245 && $counter->__tsTerminate()) {
				break;
			}
		}
		$this->assertSame(245, $counter->getCount());
		$this->assertSame(1, $counter->terminated);
	}
	
	function testTerminateModulo() {
		$counter = new Counter(500, 100);
		$i = 0;
		while($counter->__tsLoop()) {
			$i++;
			if($i >= 245 && $counter->__tsTerminate()) {
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
		$counter = new Counter(250, 100);
		$i = 0;
		while($counter->__tsLoop()) {
			$i++;
			if($i >= 245) {
				$counter->__tsTerminate();
			}
		}
		$this->assertSame(250, $counter->getCount());
		// Terminate is called 5 times before it returns false
		$this->assertSame(5, $counter->terminated);
	}

	function testExceptionLoop() {
		$counter = new Counter(250, 100);
		$counter->exceptionOn(10);
		for($i = 0; $i < 9;$i++) {
			$counter->__tsLoop();
		}
		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage("This exception is an expection.");
		$counter->__tsLoop();
	}
	
	function testExceptionStart() {
		$counter = new Counter(250, 100);
		$counter->exceptionStart = true;
		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage("exception at start");
		$counter->__tsStart();
	}

	function testExceptionFinish() {
		$counter = new Counter(250, 100);
		$counter->exceptionFinish = true;
		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage("exception at finish.");
		$counter->__tsFinish();
	}

}
