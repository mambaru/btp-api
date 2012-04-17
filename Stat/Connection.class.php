<?php

class Stat_Btp_Connection {
	protected $socket = null;
	protected $server = null;
	protected $failed = false;

	protected function __construct($srv) {
		$this->server = $srv;
	    if (!$this->server) $this->failed = true;
	}
	static $inst = null;

	/**
     * @static
     * @return Stat_Btp_Connection
     */
	public static function get() {
		if (self::$inst == null) self::$inst = new self(Core_Settings::getConfig("btp","main"));
		return self::$inst;
	}

	public function __destruct() {
		if ($this->socket) {@fclose($this->socket); $this->socket = null; }
	}
	public function isFailed() { return $this->failed; }
	public function connect() {
		if ($this->socket) return $this;
		if ($this->failed) return $this;

		$this->socket = @fsockopen($this->server['host'],$this->server['port']);
		if (!$this->socket) {
			$this->failed = true;
		}
		return $this;
	}

	protected function send($data) {
		@fwrite($this->socket,json_encode($data)."\r\n");
	}

	public function notify($method,$params) {
		if ($this->connect()->failed) return $this;
		$data = array('jsonrpc'=>'2.0','method'=>$method,'params'=>$params);
		$this->send($data);
		return $this;
	}
}
