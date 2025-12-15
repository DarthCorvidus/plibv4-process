<?php
class Dispatch {
	function __construct() {
		;
	}

	function dispatch(): void {
		if(Event::hasEvent()) {
			$event = Event::receive();
			$event->getElement()->triggerListener($event);
		return;
		}
	}
	
	static function init(): void {
		$dispatch = new Dispatch();
		pcntl_signal(SIGALRM, array($dispatch, "dispatch"));
		pcntl_async_signals(true);
	}
}