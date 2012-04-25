<?php

class Stat_Btp_Request {

	private static $obj;

	public static function getLast() {
	    if(!self::$obj)self::$obj = new self();
	    return self::$obj;
	}

	private $stat;
	private $ts;
	private $items_cnt;
	private $items;
	private $script;
	
	private $disabled;
	private $conn;

    private static $scriptname;

    public static function setScriptName($name){
        self::$scriptname = $name;
    }
	private static $data;
	public static function setExtendedData($data) {
		self::$data = $data;
	}

	public function __construct() {
		$this->stat = explode(' ', file_get_contents('/proc/self/stat'));
		$this->ts = microtime(true);
		self::$obj = $this;
        $this->script = $_SERVER['PHP_SELF'];

		$this->disabled = Stat_Btp_Connection::get()->isFailed(); //если нет соединения, отключаем сбор счётчиков
		$this->conn = Stat_Btp_Connection::get();
	}

    private function getScriptName(){
        return (self::$scriptname?:$this->script);
    }

	private function send($timings) {
		$srv = php_uname('n');
		
		if ($timings) {
			$new = explode(' ',file_get_contents('/proc/self/stat'));
			$farm = preg_replace('~\d~','',$srv);
			$this->items['SCRIPT_'.$farm][$srv] = array(
				'system' => array(1000*10*($new[14]-$this->stat[14])),
				'user' => array(1000*10*($new[13]-$this->stat[13])),
				'all' => array(round(1000000*(microtime(true)-$this->ts))),
			);
		}
		$data = array(
			'srv' => $srv,
			'script' => $this->getScriptName(),
			'time' => $this->ts,
			'items' => $this->items,
        );
		if (self::$data) $data += self::$data;
        $this->conn->notify('put',$data);

        $this->items = array();
		$this->items_cnt = 0;
	}

	public function close() {
		if (!$this->conn || $this->disabled) return;
		$this->send(true);
		$this->conn = null;
		self::$obj = null;
	}
	public function __destruct() { $this->close();}

	public function append($item) {
		if (!$this->conn || $this->disabled) return;

		if (!isset($this->items[$item['service']][$item['srv']][$item['op']])) {
			$this->items[$item['service']][$item['srv']][$item['op']] = array();
			$this->items_cnt++;
		}
		$this->items[$item['service']][$item['srv']][$item['op']][] = intval($item['ts']);
		
		if ($this->items_cnt >= 30 || microtime(true)-$this->ts>1) {
			$this->send(false);
		}
	}

}
