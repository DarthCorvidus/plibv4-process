<?php
class Event {
	private $source;
	private static $event;
	private $eventId;
	function __construct(Element $source, string $eventId) {
		$this->source = $source;
		$this->eventId = $eventId;
	}
	
	function getEventId(): string {
		return $this->eventId;
	}
	
	function getElement(): Element {
		return $this->source;
	}
	
	static function send(Event $message) {
		self::$event = $message;
		posix_kill(posix_getpid(), SIGALRM);
	}
	
	static function hasEvent(): bool {
		return self::$event != NULL;
	}
	
	static function receive(): Event {
		$event = self::$event;
		self::$event = NULL;
	return $event;
	}
}