<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use plibv4\process\Timeshare;
use plibv4\process\Task;
use \plibv4\process\RoundRobin;
use plibv4\process\TaskEnvelope;
use plibv4\process\TimeshareObservers;
class RoundRobinTest extends TestCase {
	function testConstruct() {
		$rr = new RoundRobin();
		$this->assertInstanceOf(RoundRobin::class, $rr);
		$this->assertSame(null, TestHelper::getPropertyValue($rr, "pointer"));
	}
	
	function testAdd() {
		$rr = new RoundRobin();
		$ts = new Timeshare();
		$to = new TimeshareObservers();
		$count[] = new TaskEnvelope($ts, new Counter(15), $to);
		$count[] = new TaskEnvelope($ts, new Counter(20), $to);
		$count[] = new TaskEnvelope($ts, new Counter(25), $to);
		$rr->add($count[0]);
		$this->assertSame(0, TestHelper::getPropertyValue($rr, "pointer"));
		$rr->add($count[1]);
		$this->assertSame(0, TestHelper::getPropertyValue($rr, "pointer"));
		$rr->add($count[2]);
		$this->assertSame(0, TestHelper::getPropertyValue($rr, "pointer"));
		$this->assertSame($count, TestHelper::getPropertyValue($rr, "tasks"));
	}
	
	function testGetCount() {
		$rr = new RoundRobin();
		$ts = new Timeshare();
		$to = new TimeshareObservers();
		$count0 = new TaskEnvelope($ts, new Counter(15), $to);
		$count1 = new TaskEnvelope($ts, new Counter(20), $to);
		$count2 = new TaskEnvelope($ts, new Counter(25), $to);

		$this->assertSame(0, $rr->getCount());
		$rr->add($count0);
		$this->assertSame(1, $rr->getCount());
		$rr->add($count1);
		$this->assertSame(2, $rr->getCount());
		$rr->add($count2);
		$this->assertSame(0, TestHelper::getPropertyValue($rr, "pointer"));
		$this->assertSame(3, $rr->getCount());
	}
	
	function testGetFirstCurrent() {
		$rr = new RoundRobin();
		$ts = new Timeshare();
		$to = new TimeshareObservers();
		$count0 = new TaskEnvelope($ts, new Counter(15), $to);
		$count1 = new TaskEnvelope($ts, new Counter(20), $to);
		$count2 = new TaskEnvelope($ts, new Counter(25), $to);
		$rr->add($count0);
		$this->assertSame($count0, $rr->getCurrent());
		$rr->add($count1);
		$this->assertSame($count0, $rr->getCurrent());
		$rr->add($count2);
		$this->assertSame($count0, $rr->getCurrent());
	}

	function testIncrement() {
		$rr = new RoundRobin();
		$ts = new Timeshare();
		$to = new TimeshareObservers();
		$count0 = new TaskEnvelope($ts, new Counter(15), $to);
		$count1 = new TaskEnvelope($ts, new Counter(20), $to);
		$count2 = new TaskEnvelope($ts, new Counter(25), $to);
		$rr->add($count0);
		$rr->add($count1);
		$rr->add($count2);
		$this->assertSame($count0, $rr->getCurrent());
		$rr->increment();
		$this->assertSame($count1, $rr->getCurrent());
		$rr->increment();
		$this->assertSame($count2, $rr->getCurrent());
		$rr->increment();
		$this->assertSame($count0, $rr->getCurrent());
		$rr->increment();
		$this->assertSame($count1, $rr->getCurrent());
		$rr->increment();
		$this->assertSame($count2, $rr->getCurrent());
	}

	function testGetCurrentIncrement() {
		$rr = new RoundRobin();
		$ts = new Timeshare();
		$to = new TimeshareObservers();
		$count0 = new TaskEnvelope($ts, new Counter(15), $to);
		$count1 = new TaskEnvelope($ts, new Counter(20), $to);
		$count2 = new TaskEnvelope($ts, new Counter(25), $to);
		$count3 = new TaskEnvelope($ts, new Counter(30), $to);
		$count4 = new TaskEnvelope($ts, new Counter(35), $to);
		$rr->add($count0);
		$rr->add($count1);
		$rr->add($count2);
		$rr->add($count3);
		$rr->add($count4);
		$this->assertSame($count0, $rr->getCurrentIncrement());
		$this->assertSame($count1, $rr->getCurrentIncrement());
		$this->assertSame($count2, $rr->getCurrentIncrement());
		$this->assertSame($count3, $rr->getCurrentIncrement());
		$this->assertSame($count4, $rr->getCurrentIncrement());
		$this->assertSame($count0, $rr->getCurrentIncrement());
		$this->assertSame($count1, $rr->getCurrentIncrement());
		$this->assertSame($count2, $rr->getCurrentIncrement());
		$this->assertSame($count3, $rr->getCurrentIncrement());
		$this->assertSame($count4, $rr->getCurrentIncrement());
		$this->assertSame($count0, $rr->getCurrentIncrement());
		$this->assertSame($count1, $rr->getCurrentIncrement());
	}
	
	function testRemovePointerZeroElementZero() {
		$rr = new RoundRobin();
		$ts = new Timeshare();
		$to = new TimeshareObservers();
		$count0 = new TaskEnvelope($ts, new Counter(15), $to);
		$count1 = new TaskEnvelope($ts, new Counter(20), $to);
		$count2 = new TaskEnvelope($ts, new Counter(25), $to);
		$count3 = new TaskEnvelope($ts, new Counter(30), $to);
		$count4 = new TaskEnvelope($ts, new Counter(35), $to);
		$rr->add($count0);
		$rr->add($count1);
		$rr->add($count2);
		$rr->add($count3);
		$rr->add($count4);
		$this->assertSame(5, $rr->getCount());
		$this->assertSame(0, TestHelper::getPropertyValue($rr, "pointer"));
		
		$rr->remove($count0);
		$this->assertSame(0, TestHelper::getPropertyValue($rr, "pointer"));
		$this->assertSame(4, $rr->getCount());
		$this->assertSame($count1, $rr->getCurrent());
		
		$rr->remove($count1);
		$this->assertSame(0, TestHelper::getPropertyValue($rr, "pointer"));
		$this->assertSame(3, $rr->getCount());
		$this->assertSame($count2, $rr->getCurrent());

		$rr->remove($count2);
		$this->assertSame(0, TestHelper::getPropertyValue($rr, "pointer"));
		$this->assertSame(2, $rr->getCount());
		$this->assertSame($count3, $rr->getCurrent());

		$rr->remove($count3);
		$this->assertSame(0, TestHelper::getPropertyValue($rr, "pointer"));
		$this->assertSame(1, $rr->getCount());
		$this->assertSame($count4, $rr->getCurrent());

		$rr->remove($count4);
		$this->assertSame(null, TestHelper::getPropertyValue($rr, "pointer"));
		$this->assertSame(0, $rr->getCount());
	}
	
	function testRemoveElementBeforePointer() {
		$rr = new RoundRobin();
		$ts = new Timeshare();
		$to = new TimeshareObservers();
		$count0 = new TaskEnvelope($ts, new Counter(15), $to);
		$count1 = new TaskEnvelope($ts, new Counter(20), $to);
		$count2 = new TaskEnvelope($ts, new Counter(25), $to);
		$count3 = new TaskEnvelope($ts, new Counter(30), $to);
		$count4 = new TaskEnvelope($ts, new Counter(35), $to);
		$rr->add($count0);
		$rr->add($count1);
		$rr->add($count2);
		$rr->add($count3);
		$rr->add($count4);
		$rr->getCurrentIncrement();
		$rr->getCurrentIncrement();
		$rr->getCurrentIncrement();
		$this->assertSame(3, TestHelper::getPropertyValue($rr, "pointer"));
		$this->assertSame($count3, $rr->getCurrent());
		$rr->remove($count2);
		$this->assertSame(2, TestHelper::getPropertyValue($rr, "pointer"));
		$this->assertSame($count3, $rr->getCurrent());
	}
	
	function testRemoveElementAfterPointer() {
		$rr = new RoundRobin();
		$ts = new Timeshare();
		$to = new TimeshareObservers();
		$count0 = new TaskEnvelope($ts, new Counter(15), $to);
		$count1 = new TaskEnvelope($ts, new Counter(20), $to);
		$count2 = new TaskEnvelope($ts, new Counter(25), $to);
		$count3 = new TaskEnvelope($ts, new Counter(30), $to);
		$count4 = new TaskEnvelope($ts, new Counter(35), $to);
		$rr->add($count0);
		$rr->add($count1);
		$rr->add($count2);
		$rr->add($count3);
		$rr->add($count4);
		$rr->getCurrentIncrement();
		$rr->getCurrentIncrement();
		$this->assertSame(2, TestHelper::getPropertyValue($rr, "pointer"));
		$this->assertSame($count2, $rr->getCurrent());
		$rr->remove($count4);
		$this->assertSame(2, TestHelper::getPropertyValue($rr, "pointer"));
		$this->assertSame($count2, $rr->getCurrent());
	}
	
	function testRemoveElementEqualsPointer() {
		$rr = new RoundRobin();
		$ts = new Timeshare();
		$to = new TimeshareObservers();
		$count0 = new TaskEnvelope($ts, new Counter(15), $to);
		$count1 = new TaskEnvelope($ts, new Counter(20), $to);
		$count2 = new TaskEnvelope($ts, new Counter(25), $to);
		$count3 = new TaskEnvelope($ts, new Counter(30), $to);
		$count4 = new TaskEnvelope($ts, new Counter(35), $to);
		$rr->add($count0);
		$rr->add($count1);
		$rr->add($count2);
		$rr->add($count3);
		$rr->add($count4);
		$rr->getCurrentIncrement();
		$rr->getCurrentIncrement();
		$this->assertSame(2, TestHelper::getPropertyValue($rr, "pointer"));
		$this->assertSame($count2, $rr->getCurrent());
		$rr->remove($count2);
		$this->assertSame(2, TestHelper::getPropertyValue($rr, "pointer"));
		$this->assertSame($count3, $rr->getCurrent());
	}

	function testRemoveLastElementHighestPointer() {
		$rr = new RoundRobin();
		$ts = new Timeshare();
		$to = new TimeshareObservers();
		$count0 = new TaskEnvelope($ts, new Counter(15), $to);
		$count1 = new TaskEnvelope($ts, new Counter(20), $to);
		$count2 = new TaskEnvelope($ts, new Counter(25), $to);
		$count3 = new TaskEnvelope($ts, new Counter(30), $to);
		$count4 = new TaskEnvelope($ts, new Counter(35), $to);
		$rr->add($count0);
		$rr->add($count1);
		$rr->add($count2);
		$rr->add($count3);
		$rr->add($count4);
		$rr->getCurrentIncrement();
		$rr->getCurrentIncrement();
		$rr->getCurrentIncrement();
		$rr->getCurrentIncrement();
		$this->assertSame(4, TestHelper::getPropertyValue($rr, "pointer"));
		$this->assertSame($count4, $rr->getCurrent());
		$rr->remove($count4);
		$this->assertSame(0, TestHelper::getPropertyValue($rr, "pointer"));
		$this->assertSame($count0, $rr->getCurrent());
	}
	
	function testIterate() {
		$rr = new RoundRobin();
		$ts = new Timeshare();
		$to = new TimeshareObservers();
		$count0 = new TaskEnvelope($ts, new Counter(15), $to);
		$count1 = new TaskEnvelope($ts, new Counter(20), $to);
		$count2 = new TaskEnvelope($ts, new Counter(25), $to);
		$count3 = new TaskEnvelope($ts, new Counter(30), $to);
		$count4 = new TaskEnvelope($ts, new Counter(35), $to);
		$rr->add($count0);
		$rr->add($count1);
		$rr->add($count2);
		$rr->add($count3);
		$rr->add($count4);
		$this->assertSame($count0, $rr->getItem(0));
		$this->assertSame($count1, $rr->getItem(1));
		$this->assertSame($count2, $rr->getItem(2));
		$this->assertSame($count3, $rr->getItem(3));
		$this->assertSame($count4, $rr->getItem(4));
	}
}
