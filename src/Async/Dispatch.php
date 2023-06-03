<?php
class Dispatch {
	function __construct() {
		;
	}

	function dispatch() {
		$event = Event::receive();
		$event->getElement()->triggerListener($event);
	}
	
	static function init() {
		$dispatch = new Dispatch();
		pcntl_signal(SIGALRM, array($dispatch, "dispatch"));
		pcntl_async_signals(true);
	}
}