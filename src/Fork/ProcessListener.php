<?php
interface ProcessListener {
	function onStart(Process $process);
	function onEnd(Process $process);
}
