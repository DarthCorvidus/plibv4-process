<?php
/*
 * The Problem with pcntl_signal is, that you can only set one handler for each
 * signal. Also, I hate the callback 'type'; simple functions are troublesome if
 * you need more context.
 * With Signal, it is possible to define more than one SignalHandler for each
 * signal. A catchall - Signal::call() - will then loop over them and call them
 * one by one.
 * 
 * I am no fan of Singletons, but I think that a singleton is ok here, as the
 * native functions work on the global scope as well, so there is no disadvantage
 * over using pcntl_signal; but having to do some sort of dependency injection
 * for Signal would be messier.
 */
class Signal {
	private $handlers = array();
	static private $instance;
	private function __construct() {
		
	}
	
	static function get(): Signal {
		if(self::$instance == NULL) {
			self::$instance = new Signal();
		}
	return self::$instance;
	}
	
	function addSignalHandler(int $signal, SignalHandler $handler) {
		if(!isset($this->handlers[$signal])) {
			$this->handlers[$signal] = array();
			pcntl_signal($signal, array($this, "call"));
		}
		$this->handlers[$signal][] = $handler;
	}
	
	function clearSignal(int $signal) {
		$this->handlers[$signal] = array();
		pcntl_signal($signal, SIG_DFL);
	}
	
	function call(int $signal, array $info) {
		foreach($this->handlers[$signal] as $value) {
			$value->onSignal($signal, $info);
		}
	}
}
