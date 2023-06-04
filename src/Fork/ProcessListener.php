<?php
interface ProcessListener {
	function onStart(Event $event);
	function onEnd(Event $event);
}
