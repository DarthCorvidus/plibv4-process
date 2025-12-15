<?php
interface ProcessListener {
	function onStart(Process $process): void;
	function onEnd(Process $process): void;
}
