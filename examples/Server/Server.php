<?php
class Server implements ProcessListener {
	private $socket;
	private $clients = array();
	function __construct() {
		set_time_limit(0);
		ob_implicit_flush();
		pcntl_async_signals(true);
		$address = '127.0.0.1';
		$port = 4096;
		if (($this->socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false) {
			throw new RuntimeException(sprintf("Socket creation failed: %s", socket_strerror(socket_last_error())));
		}

		if (@socket_bind($this->socket, $address, $port) === false) {
			throw new RuntimeException(sprintf("Socket bind with %s:%d failed: %s", $address, $port, socket_strerror(socket_last_error())));
		}

		if (@socket_listen($this->socket, 5) === false) {
			throw new RuntimeException(sprintf("Socket listen with %s:%d failed: %s", $address, $port, socket_strerror(socket_last_error())));
		}
	}
	
	private function onConnect($msgsock) {
		$this->clients[] = $msgsock;
		$keys = array_keys($this->clients, $msgsock);
		$runner = new RunnerServer($msgsock, $keys[0]);
		$process = new Process($runner);
		$process->addProcessListener($this);
		$process->run();
	}
	
	function run() {
		$clients = array();

		do {
			pcntl_signal_dispatch();
			#Any activity on the main socket will spawn a new process.
			$read[] = $this->socket;
			$write = NULL;
			$except = NULL;
			if(@socket_select($read, $write, $except, $tv_sec = 5) < 1) {
				$error = socket_last_error($this->socket);
				if($error!==0) {
					echo sprintf("socket_select() failed: %d %s", $error, socket_strerror($error)).PHP_EOL;
				}
				continue;
			}
			echo "A new connection has occurred.".PHP_EOL;
			if (($msgsock = socket_accept($this->socket)) === false) {
				echo "socket_accept() failed: ".socket_strerror(socket_last_error($this->socket)).PHP_EOL;
				break;
			} else {
				echo "New connection has been accepted.".PHP_EOL;
				$this->onConnect($msgsock);
			}
		} while(TRUE);
	}

	public function onEnd(Process $process) {
		$id = $process->getRunner()->getId();
		echo "Thread for client ".$id." closed.".PHP_EOL;
		socket_close($this->clients[$id]);
		unset($this->clients[$id]);
	}

	public function onStart(Process $process) {
		if($process->getRunner() instanceof RunnerServer) {
			$id = $process->getRunner()->getId();
			echo "Thread for client ".$id." spawned.".PHP_EOL;
		}
	}
}
