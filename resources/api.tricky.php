<?php
include_once('inc_htmlCurl.php');

function tricky_login($user,$pass){
	echo 'Login...',PHP_EOL;
	$data['post'] = array(
		'entrar'=>'INICIAR SESIÓN',
		'password'=>$pass,
		'usuario'=>$user,
	);

	$data['cookieFile']['file'] = $GLOBALS['config']['cookie'];
	$url = 'http://www.vendecookies.com/index.php';
	$data = html_petition($url,$data);

	if(preg_match('/id="confirmacion[^<]+<p>([^<]+)/msi',$data['pageContent'],$m)){
		return array('errorCode'=>1,'errorDescription'=>$m[1]);
	}
	return array('errorCode'=>0);
}

function tricky_register($user,$pass,$email,$referer){
	echo 'Registrando...',PHP_EOL;
	$data['post'] = array(
		'email'=>$email,
		'password'=>$pass,
		'referido'=>$referer,
		'registro'=>'REGISTRARSE',
		'usuario'=>$user,
	);

	$data['cookieFile']['file'] = $GLOBALS['config']['cookie'];
	$url = 'http://www.vendecookies.com/index.php';
	$data = html_petition($url,$data);

	if(preg_match('/id="confirmacion[^<]+<p>([^<]+)/msi',$data['pageContent'],$m)){
		return array('errorCode'=>1,'errorDescription'=>$m[1]);
	}
	return array('errorCode'=>0);
}

function tricky_getStats(){
	$data['cookieFile']['file'] = $GLOBALS['config']['cookie'];
	$url = 'http://www.vendecookies.com/';
	$data = html_petition($url,$data);

	if(!preg_match_all('/class="ingredient.*?icon-([^\.]+)[^<]+<div class="text">[<strong>]*([^<]+)/msi',$data['pageContent'],$ing)){
		echo 'ERROR! Stats not found',PHP_EOL;
		return false;
	}
	foreach($ing[1] as $k=>$i){
		if($i == 'cookie'){$GLOBALS['totalCookies'] = str_replace('.','',$ing[2][$k]);}
		echo ' # '.ucfirst($i).': '.$ing[2][$k],PHP_EOL;
	}
	echo ' # ',PHP_EOL;
	return true;
}

function tricky_openGifts(){
	$data['cookieFile']['file'] = $GLOBALS['config']['cookie'];
	$url ='http://www.vendecookies.com/index.php?p=regalos';
	$data = html_petition($url,$data);

	preg_match_all('/<a href="(index.php\?p=regalos&idr=[0-9]+)">/msi',$data['pageContent'],$m);

	foreach($m[1] as $g){
		$data['cookieFile']['file'] = $GLOBALS['config']['cookie'];
		$url = 'http://www.vendecookies.com/'.$g;
		$data = html_petition($url,$data);
	}

	// echo date('H:i:s - ').count($m[1]).' regalos abiertos',PHP_EOL;
}

function tricky_cook($user,$pass){
	// Hora máxima de ejecución
	$endTime = strtotime('+ '.rand(40,90).' minutes');

	$ip = getIP();
	echo ' # ',PHP_EOL,' # VendeCookies 2.0',PHP_EOL,' # Hora límite: ',date('H:i:s',$endTime),PHP_EOL,' # IP: ',$ip,($GLOBALS['config']['proxy']['active'] ? ' (TOR)' : ''),PHP_EOL,' # ',PHP_EOL;


	$data['cookieFile']['file'] = $GLOBALS['config']['cookie'];
	$url = 'http://www.vendecookies.com/index.php';
	$data = html_petition($url,$data);

	// Login
	if(preg_match('/Usuario registrado/msi',$data['pageContent'])){
		$login = tricky_login($user,$pass);
		if($login['errorCode']){
			echo 'ERROR! '.$login['errorCode'],PHP_EOL;
			return;
		}
	}

	sleep(1);

	// Obtener estadísticas
	$stats = tricky_getStats();
	if(!$stats){return;}


	do{
		// Si llevamos más tiempo del necesario paramos
		if(time() > $endTime){break;}

		// Esperamos 1 segundo antes de ir a cocinar
		sleep(1);

		// Entramos en la cocina
		$data['cookieFile']['file'] = $GLOBALS['config']['cookie'];
		$url = 'http://www.vendecookies.com/index.php?p=cocinar';
		$data = html_petition($url,$data);

		// Buscamos los ingredientes que nos faltan para hacer galletas
		if(preg_match_all('/class="ingredient[^"]+falta" id="ing-([0-9]+)/msi',$data['pageContent'],$m)){
			// Faltan ingredientes para cocinar galletas

			foreach($m[1] as $r){
				// Volvemos a entrar en la cocina para empezar a cocinar
				$data['cookieFile']['file'] = $GLOBALS['config']['cookie'];
				$url = 'http://www.vendecookies.com/index.php?p=cocinar';
				$data = html_petition($url,$data);

				sleep(rand(1,3));

				$data['cookieFile']['file'] = $GLOBALS['config']['cookie'];
				$url = 'http://www.vendecookies.com/index.php?p=cocinar&r='.$r;
				$data = html_petition($url,$data);

				if(!preg_match('/solicitar recurs-([0-9]+)/msi',$data['pageContent'],$res)){
					// No podemos solicitar recursos
					file_put_contents('resources/log/solicitarRecursos-'.time().'.html',$data['pageContent']);
					continue;
				}

				// Solicitamos el juego
				sleep(1);
				$data['cookieFile']['file'] = $GLOBALS['config']['cookie'];
				$data['post'] = array('resource'=>$res[1]);
				$url = 'http://www.vendecookies.com/ws/ObtainResource.php';
				$game = $data = html_petition($url,$data);


				if(preg_match('/minijuego minijuego-([0-9]+)/msi',$data['pageContent'],$game)){
					// Es un minijuego, lo lanzamos
					$data = call_user_func('tricky_game'.$game[1],$data);
					echo date('H:i:s - ').'Juego '.$game[1].': ';
				
				}elseif(preg_match('/recollir.*?onclick="location.href = \'([^\']+)/msi',$data['pageContent'],$win)){
					echo date('H:i:s - ').'Gratis: ';
					sleep(2);
					$data['cookieFile']['file'] = $GLOBALS['config']['cookie'];
					$url = 'http://www.vendecookies.com/'.$win[1];
					$data = html_petition($url,$data);
				}else{
					echo date('H:i:s - ').'Juego no reconocido',PHP_EOL;
					file_put_contents('resources/log/gameUnknown-'.time().'.html',$game['pageContent']);
					continue;
				}

				if(preg_match('/<div class="recurs">Has conseguido ([^<]+)/msi',$data['pageContent'],$prize)){
					echo $prize[1],PHP_EOL;
				}else{
					echo 'Ingredientes no encontrados',PHP_EOL;
					file_put_contents('resources/log/ingredientsNotFound-'.time().'.html',$data['pageContent']);
				}

				sleep(rand(3,5));


			}

			continue;
		}

		// A cocinar galletas
		tricky_cookie($data);

	}while(true);
}


function tricky_showUsage(){
	echo
	"Usage:\tphp ",$_SERVER['argv'][0],' -[CS] username',PHP_EOL,
		  "\tphp ",$_SERVER['argv'][0],' -R username password email [referer]',PHP_EOL,

	PHP_EOL,
	'Commands:',PHP_EOL,
	'Either long or short options are allowed.',PHP_EOL,
	" -R, --register username password email [referer]\n\t\t\t\tRegister a new user",PHP_EOL,
	" -C, --cook username\t\tCook ingredients and cookies",PHP_EOL,
	" -S, --stats username\t\tGet cookie stats",PHP_EOL,
	PHP_EOL,
	'Options:',PHP_EOL,
	" -p, --proxy host:port\t\tUse proxy",PHP_EOL,
	"     --socks5\t\t\tUse SOCK5 proxy, tor network",PHP_EOL
	;
}

/* Games */
function tricky_game001($data){
	// Tiempo de resolución del juego
	sleep(rand(3,6));

	preg_match('/MINI-([0-9]+)\.png/msi',$data['pageContent'],$ing);
	$paths = array(
		'0001'=>array(1=>4,2=>2,3=>1,4=>3),
		'0002'=>array(1=>3,2=>2,3=>4,4=>1),
		'0003'=>array(1=>4,2=>1,3=>3,4=>2),
		'0004'=>array(1=>2,2=>4,3=>3,4=>1),
		'0005'=>array(1=>2,2=>4,3=>3,4=>1),
		'0006'=>array(1=>4,2=>1,3=>3,4=>2),
		'0007'=>array(1=>3,2=>2,3=>4,4=>1),
		'0008'=>array(1=>4,2=>2,3=>1,4=>3),
		'0009'=>array(1=>1,2=>4,3=>3,4=>2),
		'0010'=>array(1=>1,2=>4,3=>3,4=>2),
		'0011'=>array(1=>3,2=>2,3=>1,4=>4),
		'0012'=>array(1=>3,2=>2,3=>1,4=>4),
		'0013'=>array(1=>1,2=>3,3=>4,4=>2),
		'0014'=>array(1=>3,2=>1,3=>2,4=>4),
		'0015'=>array(1=>2,2=>3,3=>1,4=>4),
		'0016'=>array(1=>1,2=>4,3=>2,4=>3),
		'0017'=>array(1=>2,2=>4,3=>1,4=>3),
		'0018'=>array(1=>3,2=>1,3=>4,4=>2),
		'0019'=>array(1=>3,2=>1,3=>4,4=>2),
		'0020'=>array(1=>2,2=>4,3=>1,4=>3),
		'0021'=>array(1=>3,2=>4,3=>1,4=>2),
		'0022'=>array(1=>3,2=>4,3=>1,4=>2),
		'0023'=>array(1=>3,2=>4,3=>1,4=>2),
		'0024'=>array(1=>3,2=>4,3=>1,4=>2),
		'0025'=>array(1=>4,2=>1,3=>2,4=>3),
		'0026'=>array(1=>3,2=>2,3=>4,4=>1),
		'0027'=>array(1=>4,2=>2,3=>1,4=>3),
		'0028'=>array(1=>2,2=>4,3=>3,4=>1),
		'0029'=>array(1=>3,2=>4,3=>2,4=>1),
		'0030'=>array(1=>4,2=>3,3=>1,4=>2),
		'0031'=>array(1=>3,2=>4,3=>2,4=>1),
		'0032'=>array(1=>4,2=>3,3=>1,4=>2),
		'0033'=>array(1=>4,2=>1,3=>2,4=>3),
		'0034'=>array(1=>4,2=>1,3=>2,4=>3),
		'0035'=>array(1=>2,2=>3,3=>4,4=>1),
		'0036'=>array(1=>2,2=>3,3=>4,4=>1),
		'0037'=>array(1=>1,2=>2,3=>4,4=>3),
		'0038'=>array(1=>2,2=>1,3=>3,4=>4),
		'0039'=>array(1=>2,2=>1,3=>3,4=>4),
		'0040'=>array(1=>1,2=>2,3=>4,4=>3),
		'0041'=>array(1=>1,2=>4,3=>3,4=>2),
		'0042'=>array(1=>1,2=>4,3=>3,4=>2),
		'0043'=>array(1=>3,2=>2,3=>1,4=>4),
		'0044'=>array(1=>3,2=>2,3=>1,4=>4),
		'0045'=>array(1=>4,2=>3,3=>1,4=>2),
		'0046'=>array(1=>3,2=>4,3=>2,4=>1),
		'0047'=>array(1=>4,2=>3,3=>1,4=>2),
		'0048'=>array(1=>3,2=>4,3=>2,4=>1),
	);

	$d['cookieFile']['file'] = $GLOBALS['config']['cookie'];
	$url = 'http://www.vendecookies.com/imatgeminijoc.php';
	$d = html_petition($url,$d);
	file_put_contents('game1.jpg',$d['pageContent']);


	$diffs = array();
	foreach($paths as $k=>$v){
		$c = trim(shell_exec('compare -metric AE -fuzz 30% game1.jpg resources/game1/'.$k.'.jpg diff.jpg 2>&1'));
		$diffs[$k] = $c;
		if($c == 0){break;}
	}

	asort($diffs);
	$image = key($diffs);

	copy('game1.jpg','resources/game1/processed/'.$image.'-'.time().'.jpg');

	if($diffs[$image] > 0){
		if($diffs[$image] > 50){
			print_r($diffs);
			copy('game1.jpg','resources/game1/error/'.time().'.jpg');
			exit;
		}else{
			echo 'Cambiar imagen de juego 1',PHP_EOL;
			// copy('game1.jpg','resources/game1/'.$image.'.jpg');
		}
	}

	preg_match('/inici-([0-9]+)"><img src="\/imatges\/minijuego\/001\/juego\/START-'.$ing[1].'\.png/msi',$data['pageContent'],$init);

	$end = $paths[$image][$init[1]];
	preg_match('/recollir-'.$end.'" onclick=\'location\.href = "([^"]+)/msi',$data['pageContent'],$win);

	$data['cookieFile']['file'] = $GLOBALS['config']['cookie'];
	$url = 'http://www.vendecookies.com/'.$win[1];
	$data = html_petition($url,$data);

	return $data;
}

function tricky_game002($data){
	// Tiempo de resolución del juego
	sleep(rand(14,20));

	preg_match('/if\(resultat==[0-9]+\)location\.href="([^"]+)/msi',$data['pageContent'],$win);
	$data['cookieFile']['file'] = $GLOBALS['config']['cookie'];
	$url = 'http://www.vendecookies.com/'.$win[1];
	$data = html_petition($url,$data);

	return $data;
}

function tricky_game003($data){
	// Tiempo de resolución del juego
	sleep(rand(3,12));

	preg_match('/if \(ImgFound == ImgSource\.length\) {\s*location.href = "([^"]+)/msi',$data['pageContent'],$win);
	$data['cookieFile']['file'] = $GLOBALS['config']['cookie'];
	$url = 'http://www.vendecookies.com/'.$win[1];
	$data = html_petition($url,$data);

	return $data;
}

function tricky_game004($data){
	// Tiempo de resolución del juego
	sleep(rand(3,8));

	$cookieCount = array(
		'0001'=>array('001'=>'3','002'=>'','003'=>'4','004'=>'','005'=>'3','006'=>'','007'=>'3','008'=>'','009'=>'2'),
		'0002'=>array('001'=>'3','002'=>'','003'=>'4','004'=>'','005'=>'3','006'=>'','007'=>'3','008'=>'','009'=>'2'),
		'0003'=>array('001'=>'2','002'=>'','003'=>'2','004'=>'','005'=>'2','006'=>'','007'=>'4','008'=>'','009'=>'3'),
		'0004'=>array('001'=>'2','002'=>'','003'=>'2','004'=>'','005'=>'2','006'=>'','007'=>'4','008'=>'','009'=>'3'),
		'0005'=>array('001'=>'','002'=>'','003'=>'','004'=>'3','005'=>'3','006'=>'3','007'=>'','008'=>'4','009'=>'2'),
		'0006'=>array('001'=>'','002'=>'','003'=>'','004'=>'3','005'=>'3','006'=>'3','007'=>'','008'=>'4','009'=>'2'),
		'0007'=>array('001'=>'','002'=>'','003'=>'','004'=>'3','005'=>'3','006'=>'3','007'=>'','008'=>'4','009'=>'2'),
		'0008'=>array('001'=>'','002'=>'','003'=>'','004'=>'3','005'=>'3','006'=>'3','007'=>'','008'=>'4','009'=>'2'),
		'0009'=>array('001'=>'','002'=>'','003'=>'','004'=>'3','005'=>'5','006'=>'2','007'=>'','008'=>'5','009'=>'3'),
		'0010'=>array('001'=>'','002'=>'','003'=>'','004'=>'3','005'=>'5','006'=>'2','007'=>'','008'=>'5','009'=>'3'),
		'0011'=>array('001'=>'','002'=>'','003'=>'','004'=>'3','005'=>'5','006'=>'2','007'=>'','008'=>'5','009'=>'3'),
		'0012'=>array('001'=>'','002'=>'','003'=>'','004'=>'3','005'=>'5','006'=>'2','007'=>'','008'=>'5','009'=>'3'),
		'0013'=>array('001'=>'','002'=>'4','003'=>'','004'=>'3','005'=>'2','006'=>'3','007'=>'','008'=>'','009'=>'4'),
		'0014'=>array('001'=>'','002'=>'4','003'=>'','004'=>'3','005'=>'2','006'=>'3','007'=>'','008'=>'','009'=>'4'),
		'0015'=>array('001'=>'','002'=>'4','003'=>'','004'=>'3','005'=>'2','006'=>'3','007'=>'','008'=>'','009'=>'4'),
		'0016'=>array('001'=>'','002'=>'4','003'=>'','004'=>'3','005'=>'2','006'=>'3','007'=>'','008'=>'','009'=>'4'),
		'0017'=>array('001'=>'','002'=>'2','003'=>'','004'=>'4','005'=>'4','006'=>'3','007'=>'','008'=>'','009'=>'6'),
		'0018'=>array('001'=>'','002'=>'2','003'=>'','004'=>'4','005'=>'4','006'=>'3','007'=>'','008'=>'','009'=>'6'),
		'0019'=>array('001'=>'','002'=>'2','003'=>'','004'=>'4','005'=>'4','006'=>'3','007'=>'','008'=>'','009'=>'6'),
		'0020'=>array('001'=>'','002'=>'2','003'=>'','004'=>'4','005'=>'4','006'=>'3','007'=>'','008'=>'','009'=>'6'),
		'0021'=>array('001'=>'3','002'=>'4','003'=>'3','004'=>'3','005'=>'4','006'=>'2','007'=>'','008'=>'','009'=>''),
		'0022'=>array('001'=>'3','002'=>'4','003'=>'3','004'=>'3','005'=>'4','006'=>'2','007'=>'','008'=>'','009'=>''),
		'0023'=>array('001'=>'3','002'=>'4','003'=>'3','004'=>'3','005'=>'4','006'=>'2','007'=>'','008'=>'','009'=>''),
		'0024'=>array('001'=>'3','002'=>'4','003'=>'3','004'=>'3','005'=>'4','006'=>'2','007'=>'','008'=>'','009'=>''),
		'0025'=>array('001'=>'4','002'=>'2','003'=>'4','004'=>'4','005'=>'5','006'=>'3','007'=>'','008'=>'','009'=>''),
		'0026'=>array('001'=>'4','002'=>'2','003'=>'4','004'=>'4','005'=>'5','006'=>'3','007'=>'','008'=>'','009'=>''),
		'0027'=>array('001'=>'4','002'=>'2','003'=>'4','004'=>'4','005'=>'5','006'=>'3','007'=>'','008'=>'','009'=>''),
		'0028'=>array('001'=>'4','002'=>'2','003'=>'4','004'=>'4','005'=>'5','006'=>'3','007'=>'','008'=>'','009'=>''),
		'0029'=>array('001'=>'','002'=>'','003'=>'','004'=>'4','005'=>'5','006'=>'3','007'=>'','008'=>'2','009'=>'5'),
		'0030'=>array('001'=>'','002'=>'','003'=>'','004'=>'4','005'=>'5','006'=>'3','007'=>'','008'=>'2','009'=>'5'),
		'0031'=>array('001'=>'','002'=>'','003'=>'','004'=>'4','005'=>'5','006'=>'3','007'=>'','008'=>'2','009'=>'5'),
		'0032'=>array('001'=>'','002'=>'','003'=>'','004'=>'4','005'=>'5','006'=>'3','007'=>'','008'=>'2','009'=>'5'),
		'0033'=>array('001'=>'','002'=>'','003'=>'','004'=>'5','005'=>'3','006'=>'4','007'=>'','008'=>'4','009'=>'3'),
		'0034'=>array('001'=>'','002'=>'','003'=>'','004'=>'5','005'=>'3','006'=>'4','007'=>'','008'=>'4','009'=>'3'),
		'0035'=>array('001'=>'','002'=>'','003'=>'','004'=>'5','005'=>'3','006'=>'4','007'=>'','008'=>'4','009'=>'3'),
		'0036'=>array('001'=>'','002'=>'','003'=>'','004'=>'5','005'=>'3','006'=>'4','007'=>'','008'=>'4','009'=>'3'),
		'0037'=>array('001'=>'','002'=>'5','003'=>'1','004'=>'3','005'=>'4','006'=>'3','007'=>'','008'=>'','009'=>''),
		'0038'=>array('001'=>'','002'=>'5','003'=>'1','004'=>'3','005'=>'4','006'=>'3','007'=>'','008'=>'','009'=>''),
		'0039'=>array('001'=>'','002'=>'5','003'=>'1','004'=>'3','005'=>'4','006'=>'3','007'=>'','008'=>'','009'=>''),
		'0040'=>array('001'=>'','002'=>'5','003'=>'1','004'=>'3','005'=>'4','006'=>'3','007'=>'','008'=>'','009'=>''),
		'0041'=>array('001'=>'','002'=>'3','003'=>'2','004'=>'4','005'=>'3','006'=>'4','007'=>'','008'=>'','009'=>''),
		'0042'=>array('001'=>'','002'=>'3','003'=>'2','004'=>'4','005'=>'3','006'=>'4','007'=>'','008'=>'','009'=>''),
		'0043'=>array('001'=>'','002'=>'3','003'=>'2','004'=>'4','005'=>'3','006'=>'4','007'=>'','008'=>'','009'=>''),
		'0044'=>array('001'=>'','002'=>'3','003'=>'2','004'=>'4','005'=>'3','006'=>'4','007'=>'','008'=>'','009'=>''),
	);

	$d['cookieFile']['file'] = $GLOBALS['config']['cookie'];
	$url = 'http://www.vendecookies.com/imatgeminijoc.php?t=1';
	$d = html_petition($url,$d);
	file_put_contents('game4.png',$d['pageContent']);

	for($i=1;$i<=9;$i++){
		$c = trim(shell_exec('compare -metric AE game4.png resources/game4/00'.$i.'.png diff.png 2>&1'));
		if($c == 0){$image = '00'.$i;break;}
	}
	copy('game4.png','resources/game4/processed/'.$image.'-'.time().'.png');

	preg_match('/url\("\/imatges\/minijuego\/004\/([0-9]+).*?\.minijuego-004 \.bandeja-2{background:url\("\/imatges\/minijuego\/004\/([0-9]+)/msi',$data['pageContent'],$plates);

	preg_match('/base-([0-9]+).jpg/msi',$data['pageContent'],$type);
	$plate = ($cookieCount[$plates[1]][$image] < $cookieCount[$plates[2]][$image] ? 1 : 2);
	if($type[1] == 1){$plate = ($cookieCount[$plates[1]][$image] > $cookieCount[$plates[2]][$image] ? 1 : 2);}

	preg_match('/bandeja-'.$plate.'" onclick=\'location\.href = "([^"]+)/msi',$data['pageContent'],$win);
	
	$data['cookieFile']['file'] = $GLOBALS['config']['cookie'];
	$url = 'http://www.vendecookies.com/'.$win[1];
	$data = html_petition($url,$data);

	return $data;
}

function tricky_game005($data){
	// Tiempo de resolución del juego
	sleep(rand(8,15));

	preg_match('/location.href = "([^"]+)/msi',$data['pageContent'],$win);
	$data['cookieFile']['file'] = $GLOBALS['config']['cookie'];
	$url = 'http://www.vendecookies.com/'.$win[1];
	$data = html_petition($url,$data);

	return $data;
}

function tricky_game006($data){
	// Tiempo de resolución del juego
	sleep(rand(12,20));

	preg_match('/if\(resultado==[^\)]+\){\s*location.href = "([^"]+)/msi',$data['pageContent'],$win);
	$data['cookieFile']['file'] = $GLOBALS['config']['cookie'];
	$url = 'http://www.vendecookies.com/'.$win[1];
	$data = html_petition($url,$data);

	return $data;
}

function tricky_game007($data){
	// Tiempo de resolución del juego
	sleep(rand(15,25));

	preg_match('/desabilitarTeclado=true;\s*location.href = "([^"]+)/msi',$data['pageContent'],$win);
	$data['cookieFile']['file'] = $GLOBALS['config']['cookie'];
	$url = 'http://www.vendecookies.com/'.$win[1];
	$data = html_petition($url,$data);

	return $data;
}

function tricky_game008($data){
	// Tiempo de resolución del juego
	sleep(rand(5,12));

	$order = array(
		'0001'=>'4,6,8,10,1',
		'0002'=>'4,10,5,3,1',
		'0003'=>'9,6,1,3,4',
		'0004'=>'6,4,8,1,3',
		'0005'=>'1,10,3,5,4',
		'0006'=>'10,8,5,4,3',
		'0007'=>'8,3,4,2,5',
		'0008'=>'4,8,3,5,6',
		'0009'=>'4,8,7,5,3',
		'00010'=>'8,4,5,7,3',
		'00011'=>'5,4,8,7,2',
		'00012'=>'8,4,9,7,2',
		'00013'=>'8,4,6,9,7',
		'00014'=>'3,4,6,9,5',
		'00015'=>'5,3,1,6,4',
		'00016'=>'1,3,5,6,4',
		'00017'=>'3,8,4,9,6',
		'00018'=>'4,3,8,9,6',
		'00019'=>'4,1,3,6,9',
		'00020'=>'8,1,6,3,9',
		'00021'=>'5,1,6,3,9',
		'00022'=>'5,1,6,4,9',
		'00023'=>'8,4,5,6,9',
		'00024'=>'8,4,2,6,7',
		'00025'=>'2,8,4,6,3',
		'00026'=>'4,6,2,8,3',
		'00027'=>'1,8,2,6,5',
		'00028'=>'8,2,3,6,5',
		'00029'=>'6,2,8,3,5',
		'00030'=>'2,6,4,8,10',
		'00031'=>'8,6,4,2,10',
		'00032'=>'7,4,8,10,2',
		'00033'=>'1,5,8,4,2',
		'00034'=>'1,8,5,4,6',
		'00035'=>'4,8,5,6,9',
		'00036'=>'7,2,4,8,9',
		'00037'=>'4,8,6,2,9',
		'00038'=>'4,6,8,10,7',
		'00039'=>'6,1,8,7,2',
		'00040'=>'5,3,8,7,10',
		'00041'=>'5,7,3,8,1',
		'00042'=>'7,5,2,1,8',
		'00043'=>'9,7,2,3,1',
		'00044'=>'2,10,5,3,1',
		'00045'=>'5,10,4,3,1',
		'00046'=>'10,5,4,1,8',
		'00047'=>'10,5,4,1,8',
		'00048'=>'2,6,5,1,3',
		'00049'=>'2,4,6,5,9',
		'00050'=>'2,6,4,1,5',
	);

	preg_match('/imatgeminijoc\.php\?t=([0-9]+)/msi',$data['pageContent'],$im);
	$d['cookieFile']['file'] = $GLOBALS['config']['cookie'];
	$url = 'http://www.vendecookies.com/imatgeminijoc.php';
	$d = html_petition($url,$d);
	file_put_contents('game8.jpg',$d['pageContent']);

	$diffs = array();
	foreach($order as $k=>$v){
		$c = trim(shell_exec('compare -metric AE -fuzz 30% game8.jpg resources/game8/'.$k.'.jpg diff.jpg 2>&1'));
		$diffs[$k] = $c;
		if($c == 0){break;}
	}

	asort($diffs);
	$image = key($diffs);

	copy('game8.jpg','resources/game8/processed/'.$image.'-'.time().'.jpg');

	if($diffs[$image] > 0){
		if(strlen($diffs[$image]) > 2){
			print_r($diffs);
			copy('game8.jpg','resources/game8/error/'.time().'.jpg');
			exit;
		}
		else{
			echo 'Cambiar imagen de juego 8',PHP_EOL;
			// copy('game8.jpg','resources/game8/'.$image.'.jpg');
		}
	}

	if(isset($order[$image])){
		$plats = explode(',',$order[$image]);
		preg_match('/index\.php\?p=cocinar&r=([0-9])&h="\+hash\+"([^"]+)/msi',$data['pageContent'],$h);

		$hash = '';
		foreach($plats as $plat){
			preg_match('/id="plat-([^"]+)" class="plat plat-'.$plat.'">/msi',$data['pageContent'],$res);
			$hash .= $res[1];
		}
		$hash .= $h[2];

		$url = 'http://www.vendecookies.com/index.php?p=cocinar&r='.$h[1].'&h='.$hash;
		$data['cookieFile']['file'] = $GLOBALS['config']['cookie'];
		$data = html_petition($url,$data);
	}else{
		echo date('H:i:s - ').'Tablero no encontrado en minijuego 8: '.$image,PHP_EOL;
	}

	return $data;
}

/* Cocinar galletas */
function tricky_cookie($data){
	$data['cookieFile']['file'] = $GLOBALS['config']['cookie'];
	$url = 'http://www.vendecookies.com/index.php?p=cocinar&r=1';
	$data = html_petition($url,$data);

	sleep(1);

	$data['cookieFile']['file'] = $GLOBALS['config']['cookie'];
	$data['post'] = array('resource'=>1);
	$url = 'http://www.vendecookies.com/ws/ObtainResource.php';
	$data = html_petition($url,$data);

	// Ponemos un delay de 3 segundos antes de lanzar el crono
	sleep(3);

	// Activar crono
	preg_match('/cronocookies\(([0-9]+),([0-9]+),([0-9]+)\);/msi',$data['pageContent'],$crono);
	$d['cookieFile']['file'] = $GLOBALS['config']['cookie'];
	$d['post'] = array('i'=>$crono[2],'p'=>$crono[3]);
	$url = 'http://www.vendecookies.com/ws/CreateCookies.php';
	html_petition($url,$d);

	// Captcha?
	if(!preg_match('/onclick="location.href = \'(index\.php\?p=cocinar&r=[0-9]+&h=[^\']+)/msi', $data['pageContent'],$win)){
		preg_match('/action="\/(index\.php\?p=cocinar&r=[0-9]+&h=[^"]+)/msi',$data['pageContent'],$win);

		$d['cookieFile']['file'] = $GLOBALS['config']['cookie'];
		$url = 'http://www.vendecookies.com/captcha.php';
		$im = html_petition($url,$d);

		$captcha = file_put_contents('/tmp/captcha.jpg',$im['pageContent']);
		$result = trim(shell_exec('gocr -p ./resources/captchas/ -m 258 /tmp/captcha.jpg'));
		$res = preg_replace('/[^a-z0-9]+/i','',$result);
		
		if(strlen($res) < 6){
			echo date('H:i:s - ').'Captcha no resuelto',PHP_EOL;
			file_put_contents('resources/captchas/error/'.$res.'.jpg',$im['pageContent']);
			continue;
		}

		echo date('H:i:s - ').'Captcha: '.$res,PHP_EOL;

		$data['post'] = array('texto'=>$res,'enviar'=>'Recoger');
	}

	// Tiempo de reproducción de video
	sleep(rand(46,55));
	echo date('H:i:s - ').'Cookies: ';
	/*
	echo 'Cocinando...',PHP_EOL;
	for($i=1;$i<47;$i++){
		showStatus($i,46,130);
		sleep(1);
	}
	*/

	$data['cookieFile']['file'] = $GLOBALS['config']['cookie'];
	$url = 'http://www.vendecookies.com/'.$win[1];
	$data = html_petition($url,$data);

	if(preg_match('/imatges\/disseny\/ko-[0-9]+\.png/msi',$data['pageContent'])){
		echo 'Error cocinando',PHP_EOL;
		if(isset($captcha)){file_put_contents('resources/captchas/error/'.$res.'.jpg',$im['pageContent']);}
		exit;
	}

	if(!preg_match('/<div class="recurs">[^0-9]*([0-9]+)/msi',$data['pageContent'],$prize)){
		echo 'Cookies no encontradas',PHP_EOL;
		exit;
	}
	$GLOBALS['totalCookies'] += $prize[1];
	// echo date('H:i:s - '),'Cookies: ',$prize[1],' | Total: ',$GLOBALS['totalCookies'],PHP_EOL;
	echo $prize[1],' | Total: ',$GLOBALS['totalCookies'],PHP_EOL;

	// checkGifts();
}

?>