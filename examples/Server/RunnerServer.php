<?php
class RunnerServer implements Runner {
	private $conn;
	private $clientId;
	function __construct($conn, int $clientId) {
		$this->conn = $conn;
		$this->clientId = $clientId;
	}
	
	function getId() {
		return $this->clientId;
	}
	
	public function getConnection() {
		return $this->conn;
	}
	
	private function write($message) {
		socket_write($this->conn, $message, strlen($message));
	}
	
	public function run() {
		echo "Start loop for client ".$this->clientId.PHP_EOL;
		do {
			$read[] = $this->conn;
			$write = NULL;
			$except = NULL;
			if(socket_select($read, $write, $except, $tv_sec = 5) < 1) {
				echo "No input from ".$this->clientId.PHP_EOL;
				continue;
			}
			if(false === ($buf = socket_read($this->conn, 2048, PHP_NORMAL_READ))) {
				echo "socket_read() failed: " . socket_strerror(socket_last_error($client)) . "\n";
				return;
			}
			if(!$buf = trim($buf)) {
				continue;
			}
			if($buf == 'quit') {
				echo $this->clientId." requested end of connection".PHP_EOL;
				socket_close($this->conn);
				return;
			}
			if($buf == "sleep") {
				$message = "Going to sleep for 15 seconds!".PHP_EOL;
				$this->write($message);
				sleep(15);
				continue;
			}
			if($buf == "help") {
				$message = "help - this help".PHP_EOL;
				$message .= "status - print status".PHP_EOL;
				$message .= "quit - disconnect".PHP_EOL;
				$message .= "sleep - sleep for 15 seconds".PHP_EOL;
				$this->write($message);
				continue;
			}
			$msg = sprintf("Unknown command: ".$buf);
			$talkback = sprintf("Client %d said %s", $this->clientId, $buf).PHP_EOL;
			socket_write($this->conn, $talkback, strlen($talkback));
		} while(true);
	}
}
