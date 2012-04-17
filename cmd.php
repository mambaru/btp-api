<?php

if (!isset($argv[1])) die('cmd.php get|set|delete');
$cmd = $argv[1];
$param = array();
for ($i=2;$i<count($argv);$i++) {
	$t = explode('=',$argv[$i],2);
	if (!isset($t[1])) throw new Exception();
	$param[$t[0]] = $t[1];
}

if (!isset($param['host'])) $param['host'] = '127.0.0.1';
if (!isset($param['port'])) $param['port'] = 22400;
include "../common/utils.php";
$conn = new JsonRpc_Connection(array('host'=>$param['host'],'port'=>$param['port']));
unset($param['host']);
unset($param['port']);

// это просто некий демонстрационный скрипт. пользуйтесь, если посчитаете нужным
if ($cmd=='op1') {
	print_r($conn->request('get_list',array('script'=>'?'))->get());
	exit;
	print_r($conn->request('get_graph',array('service'=>'hitlist_typed'))->get());
} elseif ($cmd == 'op2') {
	foreach ($conn->request('get_list',array('script'=>'?'))->get() as $script) {
		$list = $conn->request('get_list',array('service'=>'?','script'=>$script))->get();
		if (in_array('kyototycoon_kyoto_main',$list)) echo $script."\n";
	}

} else {
	send($cmd,$param,$conn);

}
