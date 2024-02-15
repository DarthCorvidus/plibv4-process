<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;
class CounterTest extends TestCase {
	function testCounter() {
		$counter = new Counter(500);
		while($counter->loop()) {
			
		}
		$this->assertEquals(500, $counter->getCount());
		$this->assertEquals(0, $counter->terminated);
	}
	
	function testStart() {
		$counter = new Counter(500);
		$this->assertEquals(0, $counter->started);
		$counter->start();
		$this->assertEquals(1, $counter->started);
	}

	function testFinish() {
		$counter = new Counter(500);
		$this->assertEquals(0, $counter->finished);
		$counter->finish();
		$this->assertEquals(1, $counter->finished);
	}
	
	function testTerminate() {
		$counter = new Counter(500);
		$i = 0;
		while($counter->loop()) {
			$i++;
			if($i == 245 && $counter->terminate()) {
				break;
			}
		}
		$this->assertEquals(245, $counter->getCount());
		$this->assertEquals(1, $counter->terminated);
	}
	
	function testTerminateModulo() {
		$counter = new Counter(500, 100);
		$i = 0;
		while($counter->loop()) {
			$i++;
			if($i >= 245 && $counter->terminate()) {
				break;
			}
		}
		$this->assertEquals(300, $counter->getCount());
		// Terminate is called 56 before 300 is reached and it returns false.
		$this->assertEquals(56, $counter->terminated);
	}
	/**
	 * Edge case: the max value has precedence over the next mod 100 value.
	 */
	function testTerminateEdgeCase() {
		$counter = new Counter(250, 100);
		$i = 0;
		while($counter->loop()) {
			$i++;
			if($i == 245) {
				$counter->terminate();
			}
		}
		$this->assertEquals(250, $counter->getCount());
		// Terminate is called 6 times before it returns false
		$this->assertEquals(6, $counter->terminated);
	}

}
