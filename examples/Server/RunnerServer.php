<?php
class RunnerServer implements Runner, MessageListener {
	private $conn;
	private $clientId;
	private $queue;
	function __construct($conn, int $clientId) {
		$this->conn = $conn;
		$this->clientId = $clientId;
		$this->queue = new SysVQueue(ftok(__DIR__, "a"));
		$this->queue->addListener(Signal::get(), $this);
	}
	
	function getQueue(): SysVQueue {
		return $this->queue;
	}
	
	function onMessage(\Message $message) {
		$this->write($message->getMessage()).PHP_EOL;
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
			if(@socket_select($read, $write, $except, $tv_sec = 5) < 1) {
				if(socket_last_error($this->conn)!==0) {
					echo "socket_select() failed: ".socket_strerror(socket_last_error($this->conn)).PHP_EOL;
				}
				continue;
			}
			if(false === ($buf = socket_read($this->conn, 2048, PHP_NORMAL_READ))) {
				echo "socket_read() failed: ".socket_strerror(socket_last_error($this->conn)).PHP_EOL;
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
				$this->write("Woke up.").PHP_EOL;
				continue;
			}
			
			if($buf == "status") {
				$this->queue->sendHyperwave("status", 1, posix_getppid());
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
