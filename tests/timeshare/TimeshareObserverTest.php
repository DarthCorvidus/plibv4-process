<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use \plibv4\process\Timeshare;
use \plibv4\process\Timeshared;
use \plibv4\process\TimeshareObserver;
class TimeshareObserverTest extends TestCase implements TimeshareObserver {
	private int $addCount = 0;
	private int $lastStatus = 0;
	private int $removeCount = 0;
	private int $startCount = 0;
	private int $errorCount = 0;
	private int $lastErrorStatus = 0;
	private ?Timeshared $lastAdded = null;
	private ?Timeshared $lastRemoved = null;
	private ?Timeshared $lastStarted = null;
	private ?Timeshared $lastError = null;
	private ?\Exception $lastException = null;
	function tearDown(): void {
		$this->addCount = 0;
		$this->lastStatus = 0;
		$this->removeCount = 0;
		$this->startCount = 0;
		$this->lastErrorStatus = 0;
		$this->lastAdded = null;
		$this->lastStarted = null;
		$this->lastRemoved = null;
		$this->lastException = null;
	}
	
	public function testAddObserver() {
		$timeshare = new Timeshare();
		$timeshare->addTimeshareObserver($this);
		
		$reflection = new ReflectionClass($timeshare);
		$name = $reflection->getProperty("timeshareObservers");
		$name->setAccessible(true);
		$this->assertSame(1, count($name->getValue($timeshare)));
	}

	public function testAddDuplicate() {
		$timeshare = new Timeshare();
		$timeshare->addTimeshareObserver($this);
		$timeshare->addTimeshareObserver($this);
	
		$reflection = new ReflectionClass($timeshare);
		$name = $reflection->getProperty("timeshareObservers");
		$name->setAccessible(true);
		$this->assertSame(1, count($name->getValue($timeshare)));
	}
	
	public function testOnAdd() {
		$timeshare = new Timeshare();
		$timeshare->addTimeshareObserver($this);
		$count01 = new Counter(15);
		$count02 = new Counter(20);
		
		$timeshare->addTimeshared($count01);
		$this->assertSame($count01, $this->lastAdded);
		$this->assertSame(1, $this->addCount);
		
		$timeshare->addTimeshared($count02);
		$this->assertSame($count02, $this->lastAdded);
		$this->assertSame(2, $this->addCount);
	}

	public function testOnStart() {
		$timeshare = new Timeshare();
		$timeshare->addTimeshareObserver($this);
		$count01 = new Counter(15);
		$count02 = new Counter(20);
		$count03 = new Counter(10);
		$timeshare->addTimeshared($count01);
		$timeshare->addTimeshared($count02);
		$timeshare->__tsLoop();
		
		$this->assertSame($count01, $this->lastStarted);
		$this->assertSame(1, $this->startCount);
		$timeshare->__tsLoop();

		$this->assertSame($count02, $this->lastStarted);
		$this->assertSame(2, $this->startCount);
		$timeshare->run();
		$timeshare->addTimeshared($count03);
		$timeshare->run();
		$this->assertSame($count03, $this->lastStarted);
		$this->assertSame(3, $this->startCount);
	}
	

	public function testOnRemoveFinished() {
		$timeshare = new Timeshare();
		$timeshare->addTimeshareObserver($this);
		$count01 = new Counter(15);
		$count02 = new Counter(20);
		
		$timeshare->addTimeshared($count01);
		$timeshare->addTimeshared($count02);
		$timeshare->run();
	
		$this->assertSame($count02, $this->lastRemoved);
		$this->assertSame(2, $this->removeCount);
		$this->assertSame(Timeshare::FINISH, $this->lastStatus);
	}
	
	public function testOnRemoveTerminated() {
		$timeshare = new Timeshare();
		$timeshare->addTimeshareObserver($this);
		$count01 = new Counter(15);
		$count02 = new Counter(20);
		
		$timeshare->addTimeshared($count01);
		$timeshare->addTimeshared($count02);
		$i = 0;
		while($timeshare->__tsLoop()) {
			if($i == 10) {
				$timeshare->__tsTerminate();
			}
			$i++;
		}
	
		$this->assertSame($count02, $this->lastRemoved);
		$this->assertSame(2, $this->removeCount);
		$this->assertSame(Timeshare::TERMINATE, $this->lastStatus);
	}
	
	public function testOnErrorStart() {
		$timeshare = new Timeshare();
		$timeshare->addTimeshareObserver($this);
		$count01 = new Counter(15);
		$count01->exceptionStart = true;
		$count02 = new Counter(20);
		$count02->exceptionStart = true;
		
		$timeshare->addTimeshared($count01);
		$timeshare->addTimeshared($count02);
		$timeshare->__tsLoop();
		$this->assertSame($count01, $this->lastError);
		$this->assertSame(0, $count01->getCount());
		$this->assertSame(1, $this->removeCount);
		$this->assertSame(0, $this->startCount);
		$this->assertSame(Timeshare::START, $this->lastErrorStatus);
		$this->assertSame(Timeshare::ERROR, $this->lastStatus);

		$timeshare->run();
		$this->assertSame($count02, $this->lastError);
		$this->assertSame(2, $this->removeCount);
		$this->assertSame(0, $this->startCount);
		$this->assertSame(0, $count02->getCount());
		$this->assertSame(Timeshare::START, $this->lastErrorStatus);
		$this->assertSame(Timeshare::ERROR, $this->lastStatus);
	}
	

	
	public function onAdd(Timeshare $timeshare, Timeshared $timeshared): void {
		$this->lastAdded = $timeshared;
		$this->addCount++;
	}

	public function onStart(Timeshare $timeshare, Timeshared $timeshared): void {
		$this->lastStarted = $timeshared;
		$this->startCount++;
	}
	
	public function onError(Timeshare $timeshare, Timeshared $timeshared, \Exception $exception, int $step): void {
		$this->lastError = $timeshared;
		$this->errorCount++;
		$this->lastErrorStatus = $step;
	}

	public function onRemove(Timeshare $timeshare, Timeshared $timeshared, int $status): void {
		$this->lastRemoved = $timeshared;
		$this->removeCount++;
		$this->lastStatus = $status;
	}
}