<?php
interface MessageListener {
	function onMessage(Message $message): void;
}
