<?php
class SysVQueue implements SignalHandler {
	private SysvMessageQueue $queue;
	private ?MessageListener $listener = null;
	function __construct(int $id) {
		$this->queue = msg_get_queue($id);
	}
	
	function addListener(Signal $signal, MessageListener $listener): void {
		$signal->addSignalHandler(SIGALRM, $this);
		$this->listener = $listener;
	}
	
	function onSignal(int $signal, array $info): void {
		if($this->hasMessage()) {
			$message = $this->getMessage();
			if($this->listener!=NULL) {
				$this->listener->onMessage($message);
			}
		}
	}
	
	function sendMessage(string $message, int $type): void {
		msg_send($this->queue, $type, new Message($message));
		posix_kill(posix_getpid(), SIGALRM);
	}
	
	function sendHyperwave(string $message, int $type, int $pid): void {
		msg_send($this->queue, $type, new Message($message));
		posix_kill($pid, SIGALRM);
	}
	
	function getMessage(): Message {
		$stat = $this->getStat();
		$max = (int)$stat["msg_qbytes"];
		$message = "";
		$received = 0;
		msg_receive($this->queue, 0, $received, $max, $message);
		if($message instanceof Message) {
			return $message;
		}
	throw new RuntimeException("unable to receive message");
	}
	
	function getStat(): array {
		return msg_stat_queue($this->queue);
	}
	
	function hasMessage(): bool {
		$stat = $this->getStat();
	return $stat["msg_qnum"]>0;
	}
	
	function remove(): void {
		msg_remove_queue($this->queue);
	}
	
	function clear(): void {
		while($this->hasMessage()) {
			$this->getMessage();
		}
	}
}

