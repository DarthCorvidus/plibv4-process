<?php
class SleepRunner implements Runner {
	private $seconds = 0;
	function __construct(int $seconds) {
		$this->seconds = $seconds;
	}
	public function run() {
		sleep($this->seconds);
	}
}
