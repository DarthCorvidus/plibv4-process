<?php
interface InputListener {
	function onInput(Input $input);
}

class Input implements Element {
	private $input;
	private $listener;
	function __construct() {
		;
	}
	
	public function setInputListener(InputListener $listener) {
		$this->listener = $listener;
	}
	
	public function triggerListener(Event $event) {
		$id = $event->getEventId();
		$element = $event->getElement();
		if($id=="onInput") {
			$this->listener->onInput($element);
		}
		if($id=="onOutput") {
			$this->listener->onOutput($element);
		}
	}
	
	public function getInput() {
		return $this->input;
	}
	
	function run() {
		while(true) {
			echo "> ";
			$this->input = trim(fgets(STDIN));
			if($this->input=="") {
				continue;
			}
			Event::send(new Event($this, "onInput"));
		}
	}
}