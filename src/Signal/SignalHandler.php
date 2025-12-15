<?php
interface SignalHandler {
	function onSignal(int $signal, array $info): void;
}
