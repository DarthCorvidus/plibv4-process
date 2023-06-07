<?php
class SysVQueue implements SignalHandler {
	private $queue;
	private $listener;
	function __construct(int $id) {
		$this->queue = msg_get_queue($id);
	}
	
	function addListener(Signal $signal, MessageListener $listener) {
		$signal->addSignalHandler(SIGALRM, $this);
		$this->listener = $listener;
	}
	
	function onSignal(int $signal, array $info) {
		if($this->hasMessage()) {
			$message = $this->getMessage();
			if($this->listener!=NULL) {
				$this->listener->onMessage($message);
			}
		}
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
	
	function clear() {
		while($this->hasMessage()) {
			$this->getMessage();
		}
	}
}

