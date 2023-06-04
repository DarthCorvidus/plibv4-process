<?php
class Dispatch {
	function __construct() {
		;
	}

	function dispatch($signal, $info) {
		if(Event::hasEvent()) {
			$event = Event::receive();
			$event->getElement()->triggerListener($event);
		return;
		}
	}
	
	static function init() {
		$dispatch = new Dispatch();
		pcntl_signal(SIGALRM, array($dispatch, "dispatch"));
		pcntl_async_signals(true);
	}
}