<?php
/**
 * Created by PhpStorm.
 * User: mfw
 * Date: 16/4/5
 * Time: 下午1:44
 */
class kod_socket_api{
	private $master;
	private $sockets = array(); // 不同状态的 socket 管理
	public function __construct($address,$port){
		$this->master = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) or die("socket_create() failed");
		socket_set_option($this->master, SOL_SOCKET, SO_REUSEADDR, 1) or die("socket_option() failed");
		socket_bind($this->master, $address, $port) or die("socket_bind() failed");
		socket_listen($this->master, 2) or die("socket_listen() failed");
		$this->sockets[] = $this->master;
		while(true) {
			//自动选择来消息的 socket 如果是握手 自动选择主机
			$write = NULL;
			$except = NULL;
			socket_select($this->sockets, $write, $except, NULL);
			foreach ($this->sockets as $socket) {
				//连接主机的 client
				if ($socket == $this->master){
					$client = socket_accept($this->master);
					if ($client < 0) {
						// debug
						echo "socket_accept() failed";
						continue;
					} else {
						//connect($client);
						array_push($this->sockets, $client);
						echo "connect client\n";
					}
				} else {
					$bytes = @socket_recv($socket,$buffer,2048,0);
					if($bytes == 0) return;
					if (!$this->handshake) {
						// 如果没有握手，先握手回应
						//doHandShake($socket, $buffer);
						echo "shakeHands\n";
					} else {
						// 如果已经握手，直接接受数据，并处理
						$buffer = decode($buffer);
						//process($socket, $buffer);
						echo "send file\n";
					}
				}
			}
		}

	}
}