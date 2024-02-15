<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;
class CounterTest extends TestCase {
	function testCounter() {
		$counter = new Counter(500);
		while($counter->loop()) {
			
		}
		$this->assertSame(500, $counter->getCount());
		$this->assertSame(0, $counter->terminated);
	}
	
	function testStart() {
		$counter = new Counter(500);
		$this->assertSame(0, $counter->started);
		$counter->start();
		$this->assertSame(1, $counter->started);
	}

	function testFinish() {
		$counter = new Counter(500);
		$this->assertSame(0, $counter->finished);
		$counter->finish();
		$this->assertSame(1, $counter->finished);
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
		$this->assertSame(245, $counter->getCount());
		$this->assertSame(1, $counter->terminated);
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
		while($counter->loop()) {
			$i++;
			if($i >= 245) {
				$counter->terminate();
			}
		}
		$this->assertSame(250, $counter->getCount());
		// Terminate is called 5 times before it returns false
		$this->assertSame(5, $counter->terminated);
	}

}
