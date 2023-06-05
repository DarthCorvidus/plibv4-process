<?php
class SysVQueue {
	private $queue;
	function __construct(int $id) {
		$this->queue = msg_get_queue($id);
	}
	
	function sendMessage(string $message, int $type) {
		msg_send($this->queue, $type, new Message($message));
		posix_kill(posix_getpid(), SIGALRM);
	}
	
	function sendHyperwave(string $message, int $type, int $pid) {
		msg_send($this->queue, $type, new Message($message));
		posix_kill($pid, SIGALRM);
	}
	
	function getMessage(): Message {
		$stat = $this->getStat();
		$max = $stat["msg_qbytes"];
		$message = "";
		$received = 0;
		msg_receive($this->queue, 0, $received, $stat["msg_qbytes"], $message);
	return $message;
	}
	
	function getStat(): array {
		return msg_stat_queue($this->queue);
	}
	
	function hasMessage() {
		$stat = $this->getStat();
	return $stat["msg_qnum"]>0;
	}
	
	function remove() {
		msg_remove_queue($this->queue);
	}
}

