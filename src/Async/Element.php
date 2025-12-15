<?php
interface Element {
	public function triggerListener(Event $event): void;
}
