<?php
class Event {
	private Element $source;
	private static ?Event $event = null;
	private string $eventId;
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
	
	static function send(Event $message): void {
		self::$event = $message;
		posix_kill(posix_getpid(), SIGALRM);
	}
	
	static function hasEvent(): bool {
		return self::$event != null;
	}
	
	static function receive(): Event {
		if(self::$event === null) {
			throw new RuntimeException("no event available");
		}
		$event = self::$event;
		self::$event = null;
	return $event;
	}
}