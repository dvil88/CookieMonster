<?php
include_once('resources/api.tricky.php');

$GLOBALS['config'] = array(
	'cookie'=>'',
	'proxy'=>array(
		'active'=>false,
		'host'=>'',
		'port'=>'',
		'type'=>'',
	),
);
$GLOBALS['totalCookies'] = 0;
$GLOBALS['farm'] = false;
$GLOBALS['ingredientFarm'] = false;

if($_SERVER['argc'] == 1){tricky_showUsage();exit;}

// Variables
$command = '';
$user = '';
$pass = '';
$email = '';
$referer = 'dvil88';

$argv = $_SERVER['argv'];
foreach($argv as $k=>$c){
	if($c[0] == '-'){
		switch($c){
			case '-C':
			case '--cook':
				// Leer siguiente comando
				$userName = $argv[$k+1];

				// Cargar archivo de configuración
				if(!file_exists('resources/config/'.$userName)){
					echo 'ERROR! - Username not found, please register a new account',PHP_EOL;
					tricky_showUsage();
					exit;
				}
				$GLOBALS['config']['cookie'] = 'resources/cookies/'.$userName.'.txt';
				$userConfig = json_decode(file_get_contents('resources/config/'.$userName),true);

				$user = $userConfig['user'];
				$pass = $userConfig['pass'];

				// Cocinar
				$command = 'cook';
				break;
			case '-R':
			case '--register':
				$user = $argv[++$k];
				$pass = $argv[++$k];
				$email = $argv[++$k];
				if(isset($argv[$k+1]) && $argv[$k+1][0] != '-'){$referer = $argv[$k+1];}

				// Registrar
				$command = 'register';
				break;
			case '-S':
			case '--stats':
				// Leer siguiente comando
				$userName = $argv[$k+1];

				// Cargar archivo de configuración
				if(!file_exists('resources/config/'.$userName)){
					echo 'ERROR! - Username not found, please register a new account',PHP_EOL;
					tricky_showUsage();
					exit;
				}
				$GLOBALS['config']['cookie'] = 'resources/cookies/'.$userName.'.txt';
				$userConfig = json_decode(file_get_contents('resources/config/'.$userName),true);

				// Obtener estadísticas
				$command = 'stats';
				break;
			case '-p':
			case '--proxy':
				if(!isset($argv[$k+1]) || (isset($argv[$k+1]) && $argv[$k+1][0] == '-')){
					echo 'ERROR! - You have to specify a proxy host and a port',PHP_EOL;
					tricky_showUsage();
					exit;
				}
				$proxy = explode(':',$argv[$k+1]);
				if(count($proxy) != 2){
					echo 'ERROR! - You have to specify a proxy host and a port',PHP_EOL;
					tricky_showUsage();
					exit;	
				}

				list($host,$port) = $proxy;
				$GLOBALS['config']['proxy']['active'] = true;
				$GLOBALS['config']['proxy']['host'] = $host;
				$GLOBALS['config']['proxy']['port'] = $port;
				break;
			case '--socks5':
				$GLOBALS['config']['proxy']['type'] = CURLPROXY_SOCKS5;
				break;
			case '-f':
			case '--farm':
				$GLOBALS['farm'] = true;
				break;
			case '-i':
				if(!isset($argv[$k+1]) || (isset($argv[$k+1]) && $argv[$k+1][0] == '-')){
					echo 'ERROR! - You have to specify an ingredient',PHP_EOL;
					tricky_showUsage();
					exit;
				}
				$ingredients = array('chocolate'=>'2','mantequilla'=>'3','azucar'=>'4','harina'=>'5','huevos'=>'6');
				if(!isset($ingredients[$argv[$k+1]])){
					echo 'ERROR! - You have to specify a valid ingredient',PHP_EOL;
					tricky_showUsage();
					exit;
				}
				$GLOBALS['ingredientFarm'] = $ingredients[$argv[$k+1]];
				break;
		}
	}
}

switch($command){
	case 'cook':
		tricky_cook($user,$pass);
		break;
	case 'register':
		$reg = tricky_register($user,$pass,$email,$referer);
		if($reg['errorCode']){
			echo 'ERROR! '.$reg['errorCode'],PHP_EOL;
			exit;
		}

		// Save config file
		file_put_contents('resources/config/'.$user,json_encode(array('user'=>$user,'pass'=>$pass)));
		break;
	case 'stats':
		// tricky_getStats
		break;
}



function tricky_showUsage(){
	echo
	"Usage:\tphp ",$_SERVER['argv'][0],' -[CS] username'.PHP_EOL.
		  "\tphp ",$_SERVER['argv'][0],' -R username password email [referer]'.PHP_EOL.

	PHP_EOL.
	'Commands:'.PHP_EOL.
	'Either long or short options are allowed.'.PHP_EOL.
	" -R, --register username password email [referer]\n\t\t\t\tRegister a new user".PHP_EOL.
	" -C, --cook username\t\tCook ingredients and cookies".PHP_EOL.
	" -S, --stats username\t\tGet cookie stats".PHP_EOL.
	PHP_EOL.
	'Options:'.PHP_EOL.
	" -p, --proxy host:port\t\tUse proxy".PHP_EOL.
	"     --socks5\t\t\tUse SOCK5 proxy, tor network".PHP_EOL.
	" -f, --farm\t\t\tFarm ingredients only and don't cook cookies".PHP_EOL.
	" -i ingredient\t\t\tFarm only this ingredient: chocolate, mantequilla, azucar, harina, huevos".PHP_EOL
	;
}

?>