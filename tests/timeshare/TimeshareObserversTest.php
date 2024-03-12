<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use \plibv4\process\Timeshare;
use \plibv4\process\Timeshared;
use \plibv4\process\TimeshareObserver;
use \plibv4\process\TimeshareObservers;
class TimeshareObserversTest extends TestCase implements TimeshareObserver {
	public function testAddObserver() {
		$timeshare = new TimeshareObservers();
		$timeshare->addTimeshareObserver($this);
		
		$reflection = new ReflectionClass($timeshare);
		$name = $reflection->getProperty("timeshareObservers");
		$name->setAccessible(true);
		$this->assertSame(1, count($name->getValue($timeshare)));
	}

	public function testAddDuplicate() {
		$timeshare = new TimeshareObservers();
		$timeshare->addTimeshareObserver($this);
		$timeshare->addTimeshareObserver($this);
	
		$reflection = new ReflectionClass($timeshare);
		$name = $reflection->getProperty("timeshareObservers");
		$name->setAccessible(true);
		$this->assertSame(1, count($name->getValue($timeshare)));
	}
	
	public function onAdd(Timeshare $timeshare, Timeshared $timeshared): void {
	}

	public function onStart(Timeshare $timeshare, Timeshared $timeshared): void {
	}
	
	public function onError(Timeshare $timeshare, Timeshared $timeshared, \Exception $exception, int $step): void {
	}

	public function onRemove(Timeshare $timeshare, Timeshared $timeshared, int $status): void {
	}

}