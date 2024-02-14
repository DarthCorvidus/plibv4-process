<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;
class CounterTest extends TestCase {
	function testCounter() {
		$counter = new Counter(500);
		while($counter->loop()) {
			
		}
		$this->assertEquals(500, $counter->getCount());
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
	}

}
