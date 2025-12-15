<?php
class SleepRunner implements Runner {
	private $seconds = 0;
	function __construct(int $seconds) {
		$this->seconds = $seconds;
	}
	public function run(): void {
		sleep($this->seconds);
	}
}
