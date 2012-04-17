<?php

class Core_Settings {
public static function getConfig($a = null,$b = null) {
	return array('host'=>'udp://127.0.0.1','port'=>22400);
}
}
include "Stat/Connection.class.php";
include "Stat/Request.class.php";
include "Stat/Counter.class.php";

if (count($argv)<5) die("usage: put.php SERVICE SERVER OP TIME\n");

$req = Stat_Btp_Request::getLast();
$counter = new Stat_Btp_Counter($req, array(
	'ts' => floatval($argv[4]),
	'srv' => $argv[2],
	'service' => $argv[1],
	'op' => $argv[3],
));
