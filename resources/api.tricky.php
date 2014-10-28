<?php
include_once('inc_htmlCurl.php');

function tricky_login($user,$pass){
	echo 'Login...'.PHP_EOL;
	$data['post'] = array(
		'entrar'=>'INICIAR SESIÓN',
		'password'=>$pass,
		'usuario'=>$user,
	);

	$data['cookieFile']['file'] = $GLOBALS['config']['cookie'];
	$url = 'http://www.vendecookies.com/index.php';
	$data = html_petition($url,$data);

	// Control de errores de pageContent
	if(!isset($data['pageContent']) || empty($data['pageContent'])){echo date('H:i:s - ').'Error al obtener página: '.__LINE__.PHP_EOL;exit;}

	if(preg_match('/id="confirmacion[^<]+<p>([^<]+)/msi',$data['pageContent'],$m)){
		return array('errorCode'=>1,'errorDescription'=>$m[1]);
	}
	return array('errorCode'=>0);
}

function tricky_register($user,$pass,$email,$referer){
	echo 'Registrando...'.PHP_EOL;
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

	// Control de errores de pageContent
	if(!isset($data['pageContent']) || empty($data['pageContent'])){echo date('H:i:s - ').'Error al obtener página: '.__LINE__.PHP_EOL;exit;}

	if(preg_match('/id="confirmacion[^<]+<p>([^<]+)/msi',$data['pageContent'],$m)){
		return array('errorCode'=>1,'errorDescription'=>$m[1]);
	}
	return array('errorCode'=>0);
}

function tricky_getStats(){
	$data['cookieFile']['file'] = $GLOBALS['config']['cookie'];
	$url = 'http://www.vendecookies.com/';
	$data = html_petition($url,$data);

	// Control de errores de pageContent
	if(!isset($data['pageContent']) || empty($data['pageContent'])){echo date('H:i:s - ').'Error al obtener página: '.__LINE__.PHP_EOL;return false;}

	if(!preg_match_all('/class="ingredient.*?icon-([^\.]+)[^<]+<div class="text">[<strong>]*([^<]+)/msi',$data['pageContent'],$ing)){
		echo 'ERROR! Stats not found'.PHP_EOL;
		return false;
	}
	foreach($ing[1] as $k=>$i){
		if($i == 'cookie'){$GLOBALS['totalCookies'] = str_replace('.','',$ing[2][$k]);}
		echo ' # '.ucfirst($i).': '.$ing[2][$k].PHP_EOL;
	}
	echo ' # '.PHP_EOL;
	return true;
}

function tricky_openGifts(){
	$data['cookieFile']['file'] = $GLOBALS['config']['cookie'];
	$url ='http://www.vendecookies.com/index.php?p=regalos';
	$data = html_petition($url,$data);

	$resources = array(
		'1'=>'cookies',
		'2'=>'chocolate',
		'3'=>'mantequilla',
		'4'=>'azucar',
		'5'=>'harina',
		'6'=>'huevos',
		'7'=>'coins',
		'8'=>'estrellas',
	);

	// Control de errores de pageContent
	if(!isset($data['pageContent']) || empty($data['pageContent'])){echo date('H:i:s - ').'Error al obtener página: '.__LINE__.PHP_EOL;return false;}

	preg_match_all('/<a href="(index.php\?p=regalos&idr=[0-9]+)">/msi',$data['pageContent'],$m);

	foreach($m[1] as $g){
		sleep(rand(1,2));
		$data['cookieFile']['file'] = $GLOBALS['config']['cookie'];
		$url = 'http://www.vendecookies.com/'.$g;
		$data = html_petition($url,$data);

		// Control de errores de pageContent
		if(!isset($data['pageContent']) || empty($data['pageContent'])){echo date('H:i:s - ').'Error al obtener página: '.__LINE__.PHP_EOL;return false;}

		if(preg_match('/<div id="confirmacion[^<]+<img src=\'\/imatges\/disseny\/75x75\/([0-9]+)[^<]+<p[^>]+>([^<]+)/msi',$data['pageContent'],$m)){
			echo date('H:i:s - ')."\033[1;34m".$m[2].' '.$resources[$m[1]]."\033[0m".PHP_EOL;
		}
		// file_put_contents('resources/log/gifts-'.time().'.html',$data['pageContent']);
	}

	// echo date('H:i:s - ').count($m[1]).' regalos abiertos'.PHP_EOL;
}

function tricky_cook($user,$pass){
	// Hora máxima de ejecución
	$endTime = strtotime('+ '.rand(40,90).' minutes');

	$ip = getIP();
	echo ' # '.PHP_EOL,' # VendeCookies 2.0'.PHP_EOL,' # Hora límite: ',date('H:i:s',$endTime).PHP_EOL,' # IP: ',$ip,($GLOBALS['config']['proxy']['active'] ? ' (TOR)' : '').PHP_EOL,' # '.PHP_EOL;


	$data['cookieFile']['file'] = $GLOBALS['config']['cookie'];
	$url = 'http://www.vendecookies.com/index.php';
	$data = html_petition($url,$data);

	// Control de errores de pageContent
	if(!isset($data['pageContent']) || empty($data['pageContent'])){echo date('H:i:s - ').'Error al obtener página'.PHP_EOL;return false;}

	// Login
	if(preg_match('/Usuario registrado/msi',$data['pageContent'])){
		$login = tricky_login($user,$pass);
		if($login['errorCode']){
			echo 'ERROR! '.$login['errorCode'].PHP_EOL;
			return;
		}
	}

	sleep(1);

	// Obtener estadísticas
	$stats = tricky_getStats();
	if(!$stats){return;}

	$canCook = false;
	do{
		// Esperamos 1 segundo antes de ir a cocinar
		sleep(1);

		// Entramos en la cocina
		$d = array();
		$d['cookieFile']['file'] = $GLOBALS['config']['cookie'];
		$url = 'http://www.vendecookies.com/index.php?p=cocinar';
		$data = html_petition($url,$d);

		// Control de errores de pageContent
		if(!isset($data['pageContent']) || empty($data['pageContent'])){echo date('H:i:s - ').'Error al obtener página: '.__LINE__.PHP_EOL;sleep(3);file_put_contents('resources/log/errorPage-'.__LINE__.'-'.time().'.txt',print_r($data,true));exit;continue;}

		// Activar Happy hour
		$happyHour = false;
		if(preg_match('/<div class="happyhour"><img src="\/imatges\/disseny\/happyhour\/hh-([0-9]+)/msi',$data['pageContent'],$m)){
			echo "\033[0;32mHappy Hour!!\033[0m - ";
			$happyHour = $m[1];
		}

		// Si llevamos más tiempo del necesario y no estamos en Happy Hour paramos
		if(time() > $endTime && $happyHour === false){break;}


		// Buscamos los ingredientes que nos faltan para hacer galletas
		if(preg_match_all('/class="ingredient[^"]+'.($canCook != -1 ? 'falta' : '').'" id="ing-([0-9]+)/msi',$data['pageContent'],$m) || $happyHour !== false){
			// Faltan ingredientes para cocinar galletas

			// Si hay happy hour de un ingrediente lo único que hacemos es obtener ese ingrediente
			if($happyHour !== false){$m[1] = array($happyHour);}

			foreach($m[1] as $r){
				// Volvemos a entrar en la cocina para empezar a cocinar
				$data['cookieFile']['file'] = $GLOBALS['config']['cookie'];
				$url = 'http://www.vendecookies.com/index.php?p=cocinar';
				$data = html_petition($url,$data);

				// Control de errores de pageContent
				if(!isset($data['pageContent']) || empty($data['pageContent'])){echo date('H:i:s - ').'Error al obtener página: '.__LINE__.PHP_EOL;continue 2;}

				if(preg_match('/<div id="confirmacion/msi',$data['pageContent']) && $happyHour === false){
					sleep(2);
					tricky_openGifts();
					continue;
				}

				sleep(rand(1,3));

				$data['cookieFile']['file'] = $GLOBALS['config']['cookie'];
				$url = 'http://www.vendecookies.com/index.php?p=cocinar&r='.$r;
				$data = html_petition($url,$data);

				// Control de errores de pageContent
				if(!isset($data['pageContent']) || empty($data['pageContent'])){echo date('H:i:s - ').'Error al obtener página: '.__LINE__.PHP_EOL;continue 2;}

				if(!preg_match('/solicitar recurs-([0-9]+)/msi',$data['pageContent'],$res)){
					// No podemos solicitar recursos
					file_put_contents('resources/log/solicitarRecursos-'.time().'.html',$data['pageContent']);
					continue 2;
				}

				// Control de errores de pageContent
				if(!isset($data['pageContent']) || empty($data['pageContent'])){echo date('H:i:s - ').'Error al obtener página: '.__LINE__.PHP_EOL;continue;}

				if(preg_match('/minijuego minijuego-([0-9]+)/msi',$data['pageContent'],$game)){
					// Es un minijuego, lo lanzamos
					if(!function_exists('tricky_game'.$game[1])){echo 'No existe el juego'.PHP_EOL;file_put_contents('resources/log/gameNotFound-'.time().'.html',$data['pageContent']);exit;}
					$data = call_user_func('tricky_game'.$game[1],$data);
					echo date('H:i:s - ').'Juego '.$game[1].': ';
				
				}elseif(preg_match('/recollir.*?onclick="location.href = \'([^\']+)/msi',$data['pageContent'],$win)){
					echo date('H:i:s - ').'Gratis: ';
					sleep(2);
					$data['cookieFile']['file'] = $GLOBALS['config']['cookie'];
					$url = 'http://www.vendecookies.com/'.$win[1];
					$data = html_petition($url,$data);
				}else{
					echo date('H:i:s - ').'Juego no reconocido, volvemos a la cocina'.PHP_EOL;
					file_put_contents('resources/log/gameUnknown-'.time().'.html',$data['pageContent']);
					continue 2;
				}

				if($data === false){
					// No es seguro seguir, volvemos a la cocina
					continue 2;
				}

				// Control de errores de pageContent
				if(!isset($data['pageContent']) || empty($data['pageContent'])){echo date('H:i:s - ').'Error al obtener página: '.__LINE__.PHP_EOL;continue 2;}

				if(preg_match('/<div class="recurs">Has conseguido ([^<]+)/msi',$data['pageContent'],$prize)){
					echo $prize[1].PHP_EOL;
				}else{
					echo "\033[0;31mIngredientes no encontrados\033[0m".PHP_EOL;
					file_put_contents('resources/log/ingredientsNotFound-'.time().'.html',$data['pageContent']);
					// return;
				}


				sleep(rand(3,5));
			}

			continue;
		}

		// A cocinar galletas
		$canCook = true;
		$canCook = tricky_cookie($data);
		// $canCook = -1;
	}while(true);
}

function tricky_showUsage(){
	echo
	"Usage:\tphp ",$_SERVER['argv'][0],' -[CS] username'.PHP_EOL,
		  "\tphp ",$_SERVER['argv'][0],' -R username password email [referer]'.PHP_EOL,

	PHP_EOL,
	'Commands:'.PHP_EOL,
	'Either long or short options are allowed.'.PHP_EOL,
	" -R, --register username password email [referer]\n\t\t\t\tRegister a new user".PHP_EOL,
	" -C, --cook username\t\tCook ingredients and cookies".PHP_EOL,
	" -S, --stats username\t\tGet cookie stats".PHP_EOL,
	PHP_EOL,
	'Options:'.PHP_EOL,
	" -p, --proxy host:port\t\tUse proxy".PHP_EOL,
	"     --socks5\t\t\tUse SOCK5 proxy, tor network".PHP_EOL
	;
}

function solveCaptcha(){
	$coords = array(
		1=>array('x'=>'27','y'=>'14'),
		2=>array('x'=>'36','y'=>'14'),
		3=>array('x'=>'45','y'=>'14'),
		4=>array('x'=>'54','y'=>'14'),
		5=>array('x'=>'63','y'=>'14'),
		6=>array('x'=>'72','y'=>'14'),
	);

	$result ='';for($i=1;$i<=6;$i++){
		shell_exec('convert /tmp/captcha.jpg -negate -type Grayscale -crop 10x14+'.$coords[$i]['x'].'+'.$coords[$i]['y'].' /tmp/letter'.$i.'.jpg 2>&1');
		$result .= trim(shell_exec('gocr -p ./resources/captchas/old_database/ -m 258 -a 83 /tmp/letter'.$i.'.jpg'));
	}
	$res = preg_replace('/[^a-z0-9]+/i','',$result);
		
	if(strlen($res) != 6){
		$time = time();
		copy('/tmp/captcha.jpg','resources/captchas/error/'.$time.'.jpg');

		return false;
	}

	return $res;
}

function solveCaptcha2($imagePath = ''){
	$pixel_getColor = function($im,$x,$y){
		$rgb = imagecolorat($im,$x,$y);
		$r = ($rgb >> 16) & 0xFF;
		$g = ($rgb >> 8) & 0xFF;
		$b = $rgb & 0xFF;
		return array('r'=>$r,'g'=>$g,'b'=>$b);
	};
	$pixel_isBlank = function($c,$perc = 100){
		$limit = 255*($perc/100);
		if($c['r'] < $limit || $c['g'] < $limit || $c['b'] < $limit){return false;}
		return true;
	};

	$im = imagecreatefromjpeg($imagePath);
	imagefilter($im,IMG_FILTER_NEGATE);
	imagefilter($im,IMG_FILTER_GRAYSCALE);
	imagefilter($im,IMG_FILTER_BRIGHTNESS,6);
	imagefilter($im,IMG_FILTER_CONTRAST,-30);

	$w = imagesx($im);
	$h = imagesy($im);

	$cuts = array(
		array(28,14),
		array(36,14),
		array(45,14),
		array(54,14),
		array(63,14),
		array(72,14)
	);

	$left = 2;
	$pad = 0;
	$imageclean = imagecreatetruecolor($w,$h);
	$white = imagecolorallocate($imageclean,255,255,255);
	$black = imagecolorallocate($imageclean,0,0,0);
	imagefill($imageclean,0,0,$white);
	foreach($cuts as $cut){
		imagecopy($imageclean,$im,$left,14,$cut[0]+$pad,$cut[1]+$pad,9,16);
		$left += 14;
	}

	/* Cleanup */
	$perc = 60;
	for($y=0;$y<$h;$y++){
		for($x=0;$x<$w;$x++){
			$c = $pixel_getColor($imageclean,$x,$y);
			if($pixel_isBlank($c,$perc)){imagesetpixel($imageclean,$x,$y,$white);continue;}
			imagesetpixel($imageclean,$x,$y,$black);
		}
	}

	imagepng($imageclean,'/tmp/captcha_p.png');
	imagedestroy($imageclean);

	$result = trim(shell_exec('gocr -p ./resources/captchas/ -m 258 /tmp/captcha_p.png 2>&1'));
	if(preg_match('/ERROR pnm.c L[0-9]*: unexpected EOF/',$result)){
		echo 'CAPTCHA_ERROR';exit;
	}
	$result = preg_replace('/[^a-z0-9]+/i','',$result);	
	if(strlen($result) != 6){
		copy('/tmp/captcha.jpg','resources/captchas/error/'.$result.'.jpg');
		copy('/tmp/captcha_p.png','resources/captchas/error/'.$result.'_p.png');
		return false;
	}

	return $result;
}

/* Games */
function tricky_game001($data){
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
		'0025'=>array(1=>4,2=>1,3=>3,4=>2),
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
			// echo 'Cambiar imagen de juego 1'.PHP_EOL;
			copy('game1.jpg','resources/game1/error/'.$image.'.jpg');
		}
	}

	preg_match('/inici-([0-9]+)"><img src="\/imatges\/minijuego\/001\/juego\/START-'.$ing[1].'\.png/msi',$data['pageContent'],$init);

	$end = $paths[$image][$init[1]];
	preg_match('/recollir-'.$end.'" onclick=\'location\.href = "([^"]+)/msi',$data['pageContent'],$win);

	// Tiempo de resolución del juego
	sleep(rand(3,6));

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
	sleep(rand(12,25));

	// file_put_contents('resources/log/game3-'.time().'.html',$data['pageContent']);

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
		'0001'=>array('001'=>'003','002'=>'','003'=>'4','004'=>'','005'=>'003','006'=>'','007'=>'003','008'=>'','009'=>'2'),
		'0002'=>array('001'=>'003','002'=>'','003'=>'4','004'=>'','005'=>'003','006'=>'','007'=>'003','008'=>'','009'=>'2'),
		'0003'=>array('001'=>'2','002'=>'','003'=>'2','004'=>'','005'=>'2','006'=>'','007'=>'4','008'=>'','009'=>'003'),
		'0004'=>array('001'=>'2','002'=>'','003'=>'2','004'=>'','005'=>'2','006'=>'','007'=>'4','008'=>'','009'=>'003'),
		'0005'=>array('001'=>'','002'=>'','003'=>'','004'=>'003','005'=>'003','006'=>'003','007'=>'','008'=>'4','009'=>'2'),
		'0006'=>array('001'=>'','002'=>'','003'=>'','004'=>'003','005'=>'003','006'=>'003','007'=>'','008'=>'4','009'=>'2'),
		'0007'=>array('001'=>'','002'=>'','003'=>'','004'=>'003','005'=>'003','006'=>'003','007'=>'','008'=>'4','009'=>'2'),
		'0008'=>array('001'=>'','002'=>'','003'=>'','004'=>'003','005'=>'003','006'=>'003','007'=>'','008'=>'4','009'=>'2'),
		'0009'=>array('001'=>'','002'=>'','003'=>'','004'=>'003','005'=>'5','006'=>'2','007'=>'','008'=>'5','009'=>'003'),
		'0010'=>array('001'=>'','002'=>'','003'=>'','004'=>'003','005'=>'5','006'=>'2','007'=>'','008'=>'5','009'=>'003'),
		'0011'=>array('001'=>'','002'=>'','003'=>'','004'=>'003','005'=>'5','006'=>'2','007'=>'','008'=>'5','009'=>'003'),
		'0012'=>array('001'=>'','002'=>'','003'=>'','004'=>'003','005'=>'5','006'=>'2','007'=>'','008'=>'5','009'=>'003'),
		'0013'=>array('001'=>'','002'=>'4','003'=>'','004'=>'003','005'=>'2','006'=>'003','007'=>'','008'=>'','009'=>'4'),
		'0014'=>array('001'=>'','002'=>'4','003'=>'','004'=>'003','005'=>'2','006'=>'003','007'=>'','008'=>'','009'=>'4'),
		'0015'=>array('001'=>'','002'=>'4','003'=>'','004'=>'003','005'=>'2','006'=>'003','007'=>'','008'=>'','009'=>'4'),
		'0016'=>array('001'=>'','002'=>'4','003'=>'','004'=>'003','005'=>'2','006'=>'003','007'=>'','008'=>'','009'=>'4'),
		'0017'=>array('001'=>'','002'=>'2','003'=>'','004'=>'4','005'=>'4','006'=>'003','007'=>'','008'=>'','009'=>'6'),
		'0018'=>array('001'=>'','002'=>'2','003'=>'','004'=>'4','005'=>'4','006'=>'003','007'=>'','008'=>'','009'=>'6'),
		'0019'=>array('001'=>'','002'=>'2','003'=>'','004'=>'4','005'=>'4','006'=>'003','007'=>'','008'=>'','009'=>'6'),
		'0020'=>array('001'=>'','002'=>'2','003'=>'','004'=>'4','005'=>'4','006'=>'003','007'=>'','008'=>'','009'=>'6'),
		'0021'=>array('001'=>'003','002'=>'4','003'=>'003','004'=>'003','005'=>'4','006'=>'2','007'=>'','008'=>'','009'=>''),
		'0022'=>array('001'=>'003','002'=>'4','003'=>'003','004'=>'003','005'=>'4','006'=>'2','007'=>'','008'=>'','009'=>''),
		'0023'=>array('001'=>'003','002'=>'4','003'=>'003','004'=>'003','005'=>'4','006'=>'2','007'=>'','008'=>'','009'=>''),
		'0024'=>array('001'=>'003','002'=>'4','003'=>'003','004'=>'003','005'=>'4','006'=>'2','007'=>'','008'=>'','009'=>''),
		'0025'=>array('001'=>'4','002'=>'2','003'=>'4','004'=>'4','005'=>'5','006'=>'003','007'=>'','008'=>'','009'=>''),
		'0026'=>array('001'=>'4','002'=>'2','003'=>'4','004'=>'4','005'=>'5','006'=>'003','007'=>'','008'=>'','009'=>''),
		'0027'=>array('001'=>'4','002'=>'2','003'=>'4','004'=>'4','005'=>'5','006'=>'003','007'=>'','008'=>'','009'=>''),
		'0028'=>array('001'=>'4','002'=>'2','003'=>'4','004'=>'4','005'=>'5','006'=>'003','007'=>'','008'=>'','009'=>''),
		'0029'=>array('001'=>'','002'=>'','003'=>'','004'=>'4','005'=>'5','006'=>'003','007'=>'','008'=>'2','009'=>'5'),
		'0030'=>array('001'=>'','002'=>'','003'=>'','004'=>'4','005'=>'5','006'=>'003','007'=>'','008'=>'2','009'=>'5'),
		'0031'=>array('001'=>'','002'=>'','003'=>'','004'=>'4','005'=>'5','006'=>'003','007'=>'','008'=>'2','009'=>'5'),
		'0032'=>array('001'=>'','002'=>'','003'=>'','004'=>'4','005'=>'5','006'=>'003','007'=>'','008'=>'2','009'=>'5'),
		'0033'=>array('001'=>'','002'=>'','003'=>'','004'=>'5','005'=>'003','006'=>'4','007'=>'','008'=>'4','009'=>'003'),
		'0034'=>array('001'=>'','002'=>'','003'=>'','004'=>'5','005'=>'003','006'=>'4','007'=>'','008'=>'4','009'=>'003'),
		'0035'=>array('001'=>'','002'=>'','003'=>'','004'=>'5','005'=>'003','006'=>'4','007'=>'','008'=>'4','009'=>'003'),
		'0036'=>array('001'=>'','002'=>'','003'=>'','004'=>'5','005'=>'003','006'=>'4','007'=>'','008'=>'4','009'=>'003'),
		'0037'=>array('001'=>'','002'=>'5','003'=>'1','004'=>'003','005'=>'4','006'=>'003','007'=>'','008'=>'','009'=>''),
		'0038'=>array('001'=>'','002'=>'5','003'=>'1','004'=>'003','005'=>'4','006'=>'003','007'=>'','008'=>'','009'=>''),
		'0039'=>array('001'=>'','002'=>'5','003'=>'1','004'=>'003','005'=>'4','006'=>'003','007'=>'','008'=>'','009'=>''),
		'0040'=>array('001'=>'','002'=>'5','003'=>'1','004'=>'003','005'=>'4','006'=>'003','007'=>'','008'=>'','009'=>''),
		'0041'=>array('001'=>'','002'=>'003','003'=>'2','004'=>'4','005'=>'003','006'=>'4','007'=>'','008'=>'','009'=>''),
		'0042'=>array('001'=>'','002'=>'003','003'=>'2','004'=>'4','005'=>'003','006'=>'4','007'=>'','008'=>'','009'=>''),
		'0043'=>array('001'=>'','002'=>'003','003'=>'2','004'=>'4','005'=>'003','006'=>'4','007'=>'','008'=>'','009'=>''),
		'0044'=>array('001'=>'','002'=>'003','003'=>'2','004'=>'4','005'=>'003','006'=>'4','007'=>'','008'=>'','009'=>''),
	);

	$d['cookieFile']['file'] = $GLOBALS['config']['cookie'];
	$url = 'http://www.vendecookies.com/imatgeminijoc.php?t=1';
	$d = html_petition($url,$d);

	// Control de errores de pageContent
	if(!isset($d['pageContent']) || empty($d['pageContent'])){echo date('H:i:s - ').'Error al obtener página: '.__LINE__.PHP_EOL;return false;}

	file_put_contents('game4.png',$d['pageContent']);

	for($i=1;$i<=9;$i++){
		$c = trim(shell_exec('compare -metric AE game4.png resources/game4/00'.$i.'.png diff.png 2>&1'));
		if($c == 0){$image = '00'.$i;break;}
	}

	if($c != 0){
		echo 'No coinciden las imágenes'.PHP_EOL;
		copy('game4.png','resources/game4/error/'.time().'.png');
		return false;
	}

	copy('game4.png','resources/game4/processed/'.$image.'-'.time().'.png');

	preg_match('/url\("\/imatges\/minijuego\/004\/([0-9]+).*?\.minijuego-004 \.bandeja-2{background:url\("\/imatges\/minijuego\/004\/([0-9]+)/msi',$data['pageContent'],$plates);

	preg_match('/base-([0-9]+).jpg/msi',$data['pageContent'],$type);
	$plate = ($cookieCount[$plates[1]][$image] < $cookieCount[$plates[2]][$image] ? 1 : 2);
	if($type[1] == 1){$plate = ($cookieCount[$plates[1]][$image] > $cookieCount[$plates[2]][$image] ? 1 : 2);}

	preg_match('/bandeja-'.$plate.'" onclick=\'location\.href = "([^"]+)/msi',$data['pageContent'],$win);
	
	// Tiempo de resolución del juego
	sleep(rand(3,8));

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

	// Fallo en un 10% de las veces
	if(rand(0,100)%10 == 0){preg_match('/else{\s*location.href = "([^"]+)/msi',$data['pageContent'],$win);echo 'Provocamos error en el ahorcado'.PHP_EOL;}

	$data['cookieFile']['file'] = $GLOBALS['config']['cookie'];
	$url = 'http://www.vendecookies.com/'.$win[1];
	$data = html_petition($url,$data);

	return $data;
}

function tricky_game008($data){
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

	// Control de errores de pageContent
	if(!isset($d['pageContent']) || empty($d['pageContent'])){echo date('H:i:s - ').'Error al obtener página: '.__LINE__.PHP_EOL;return false;}

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
			// echo 'Cambiar imagen de juego 8'.PHP_EOL;
			copy('game8.jpg','resources/game8/error/'.$image.'.jpg');
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

		// Tiempo de resolución del juego
		sleep(rand(5,12));

		$url = 'http://www.vendecookies.com/index.php?p=cocinar&r='.$h[1].'&h='.$hash;
		$data['cookieFile']['file'] = $GLOBALS['config']['cookie'];
		$data = html_petition($url,$data);
	}else{
		echo date('H:i:s - ').'Tablero no encontrado en minijuego 8: '.$image.PHP_EOL;
	}

	return $data;
}

function tricky_game010($data){
	$d['cookieFile']['file'] = $GLOBALS['config']['cookie'];
	$url = 'http://www.vendecookies.com/imatgeminijoc.php';
	$d = html_petition($url,$d);

	// Control de errores de pageContent
	if(!isset($d['pageContent']) || empty($d['pageContent'])){echo date('H:i:s - ').'Error al obtener página: '.__LINE__.PHP_EOL;return false;}

	$singles = array(
		'0001'=>'4',
		'0002'=>'4',
		'0003'=>'4',
		'0004'=>'4',
		'0005'=>'4',
		'0006'=>'4',
		'0007'=>'4',
		'0008'=>'4',
		'0009'=>'4',
		'0010'=>'4',
		'0011'=>'5',
		'0012'=>'5',
		'0013'=>'5',
		'0014'=>'5',
		'0014'=>'5',
		'0015'=>'5',
		'0017'=>'5',
		'0018'=>'5',
		'0019'=>'5',
		'0020'=>'5',
		'0021'=>'1',
		'0022'=>'1',
		'0023'=>'1',
		'0024'=>'1',
		'0025'=>'1',
		'0026'=>'1',
		'0027'=>'1',
		'0028'=>'1',
		'0029'=>'1',
		'0030'=>'1',
		'0031'=>'7',
		'0032'=>'7',
		'0033'=>'7',
		'0034'=>'7',
		'0035'=>'7',
		'0036'=>'7',
		'0037'=>'7',
		'0038'=>'7',
		'0039'=>'7',
		'0040'=>'7',
		'0041'=>'3',
		'0042'=>'3',
		'0043'=>'3',
		'0044'=>'3',
		'0045'=>'3',
		'0046'=>'3',
		'0047'=>'3',
		'0048'=>'3',
		'0049'=>'3',
		'0050'=>'3',
		'0051'=>'8',
		'0052'=>'8',
		'0053'=>'8',
		'0054'=>'8',
		'0055'=>'8',
		'0056'=>'8',
		'0057'=>'8',
		'0058'=>'8',
		'0059'=>'8',
		'0060'=>'8',
		'0061'=>'2',
		'0062'=>'2',
		'0063'=>'2',
		'0064'=>'2',
		'0065'=>'2',
		'0066'=>'2',
		'0067'=>'2',
		'0068'=>'2',
		'0069'=>'2',
		'0070'=>'2',
		'0071'=>'6',
		'0072'=>'6',
		'0073'=>'6',
		'0074'=>'6',
		'0075'=>'6',
		'0076'=>'6',
		'0077'=>'6',
		'0078'=>'6',
		'0079'=>'6',
		'0080'=>'6',
	);

	file_put_contents('game10.jpg',$d['pageContent']);
	shell_exec('convert game10.jpg -crop 356x356+50+51 crop10.jpg');

	$diffs = array();
	foreach($singles as $k=>$v){
		$c = trim(shell_exec('compare -metric AE -fuzz 50% crop10.jpg resources/game10/'.$k.'.jpg /dev/null 2>&1'));
		$diffs[$k] = $c;
		if($c == 0){break;}
	}

	asort($diffs);
	$image = key($diffs);

	copy('game10.jpg','resources/game10/processed/'.$image.'-'.time().'.jpg');

	if($diffs[$image] > 0){
		if($diffs[$image] > 100){
			$diffs = array_slice($diffs,0,10);
			print_r($diffs);

			echo "\033[0;31mError en juego 10\033[0m".PHP_EOL;
			copy('crop10.jpg','resources/game10/error/'.time().'.jpg');
			return false;
		}
		echo 'Cambiar imagen de juego 10:  '.$image.PHP_EOL;
		copy('crop10.jpg','resources/game10/'.$image.'.jpg');
	}

	if(preg_match('/<div class="opcio opcio-'.$singles[$image].'" onclick=\'location.href = "([^"]+)/msi',$data['pageContent'],$win)){
		// Tiempo de resolución del juego
		sleep(rand(8,15));

		$data['cookieFile']['file'] = $GLOBALS['config']['cookie'];
		$url = 'http://www.vendecookies.com/'.$win[1];
		$data = html_petition($url,$data);

		return $data;
	}else{
		echo 'Error emparejando galletas'.PHP_EOL;
		return false;
	}
}

function tricky_game011($data){
	$d['cookieFile']['file'] = $GLOBALS['config']['cookie'];
	$url = 'http://www.vendecookies.com/imatgeminijoc.php';
	$d = html_petition($url,$d);

	// Control de errores de pageContent
	if(!isset($d['pageContent']) || empty($d['pageContent'])){echo date('H:i:s - ').'Error al obtener página: '.__LINE__.PHP_EOL;return false;}

	file_put_contents('game11.jpg',$d['pageContent']);

	$weights = array(
		'0001'=>'1',
		'0002'=>'2',
		'0003'=>'1',
		'0004'=>'2',
		'0005'=>'1',
		'0006'=>'1',
		'0007'=>'1',
		'0008'=>'2',
		'0009'=>'2',
		'00010'=>'1',
		'00011'=>'2',
		'00012'=>'1',
		'00013'=>'2',
		'00014'=>'2',
		'00015'=>'1',
		'00016'=>'2',
		'00017'=>'1',
		'00018'=>'2',
		'00019'=>'1',
		'00020'=>'2',
		'00021'=>'2',
		'00022'=>'1',
		'00023'=>'2',
		'00024'=>'1',
		'00025'=>'1',
		'00026'=>'2',
		'00027'=>'2',
		'00028'=>'1',
		'00029'=>'1',
		'00030'=>'2',
		'00031'=>'1',
		'00032'=>'2',
		'00033'=>'1',
		'00034'=>'2',
		'00035'=>'1',
		'00036'=>'2',
		'00037'=>'1',
		'00038'=>'2',
		'00039'=>'1',
		'00040'=>'2',
		'00041'=>'1',
		'00042'=>'1',
		'00043'=>'2',
		'00044'=>'1',
		'00045'=>'1',
		'00046'=>'1',
		'00047'=>'2',
		'00048'=>'1',
		'00049'=>'2',
		'00050'=>'1',
		'00051'=>'2',
		'00052'=>'1',
		'00053'=>'2',
		'00054'=>'1',
		'00055'=>'1',
		'00056'=>'2',
		'00057'=>'1',
		'00058'=>'2',
		'00059'=>'2',
		'00060'=>'1',
	);

	$diffs = array();
	foreach($weights as $k=>$v){
		$c = trim(shell_exec('compare -metric AE -fuzz 50% game11.jpg resources/game11/'.$k.'.jpg /dev/null 2>&1'));
		$diffs[$k] = $c;
		if($c == 0){break;}
	}

	asort($diffs);
	$image = key($diffs);

	copy('game11.jpg','resources/game11/processed/'.$image.'-'.time().'.jpg');

	if($diffs[$image] > 0){
		if($diffs[$image] > 100){
			//print_r($diffs);
			echo "\033[0;31mError en juego 11\033[0m".PHP_EOL;
			copy('game11.jpg','resources/game11/error/'.time().'.jpg');
			return false;
		}
		echo 'Cambiar imagen de juego 11: '.$image.PHP_EOL;
		copy('game11.jpg','resources/game11/'.$image.'.jpg');		
	}

	if(preg_match('/bascula-'.$weights[$image].'" onclick=\'location.href = "([^\"]+)/msi',$data['pageContent'],$win)){
		// Tiempo de resolución del juego
		sleep(rand(3,8));

		$data['cookieFile']['file'] = $GLOBALS['config']['cookie'];
		$url = 'http://www.vendecookies.com/'.$win[1];
		$data = html_petition($url,$data);

		return $data;
	}else{
		echo 'Error pesando galletas'.PHP_EOL;
		return false;
	}
}

/* Cocinar galletas */
function tricky_cookie($data){
	$data['cookieFile']['file'] = $GLOBALS['config']['cookie'];
	$url = 'http://www.vendecookies.com/index.php?p=cocinar&r=1';
	$data = html_petition($url,$data);

	// Control de errores de pageContent
	if(!isset($data['pageContent']) || empty($data['pageContent'])){echo date('H:i:s - ').'Error al obtener página: '.__LINE__.PHP_EOL;return false;}

	// Comprobamos si ha dado error de que no hay suficientes recursos
	if(preg_match('/imatges\/disseny\/ko-00\.png/msi',$data['pageContent'])){
		echo 'No tenemos suficientes recursos para cocinar.'.PHP_EOL;
		return -1;
	}

	// Ponemos un delay de 2 segundos antes de lanzar el crono
	sleep(2);

	// Activar crono
	if(!preg_match('/cronocookies\(([0-9]+),([0-9]+),([0-9]+)\);/msi',$data['pageContent'],$crono)){echo date('H:i:s - ').'Error al iniciar crono: '.__LINE__.PHP_EOL;file_put_contents('resources/log/crono-'.time().'.html',$data['pageContent']);exit;}
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

		// Control de errores de pageContent
		if(!isset($im['pageContent'])){echo date('H:i:s - ').'Error al obtener página: '.__LINE__.PHP_EOL;return false;}

		$captcha = file_put_contents('/tmp/captcha.jpg',$im['pageContent']);
		
		/*
		$result = trim(shell_exec('gocr -p ./resources/captchas/ -m 258 -a 83 /tmp/captcha.jpg'));
		$res = preg_replace('/[^a-z0-9]+/i','',$result);
		
		if(strlen($res) != 6){
			// echo date('H:i:s - ').'Captcha no resuelto'.PHP_EOL;
			// file_put_contents('resources/captchas/error/'.$res.'.jpg',$im['pageContent']);
			// return;

			$res = solveCaptcha();
			if($res === false){
				echo date('H:i:s - ')."\033[0;31mCaptcha no resuelto\033[0m".PHP_EOL;
				return;
			}
		}
		*/
	
		// $res = solveCaptcha();
		$res = solveCaptcha2('/tmp/captcha.jpg');
		if($res === false){
			echo date('H:i:s - ')."\033[0;31mCaptcha no resuelto\033[0m".PHP_EOL;
			exit;
			return;
		}

		echo date('H:i:s - ').'Captcha: '.$res.PHP_EOL;

		$data['post'] = array('texto'=>$res,'enviar'=>'Recoger');
	}

	// Tiempo de reproducción de video
	sleep(rand(47,55));
	echo date('H:i:s - ').'Cookies: ';

	$data['cookieFile']['file'] = $GLOBALS['config']['cookie'];
	$url = 'http://www.vendecookies.com/'.$win[1];
	$data = html_petition($url,$data);

	// Control de errores de pageContent
	if(!isset($data['pageContent']) || empty($data['pageContent'])){echo date('H:i:s - ').'Error al obtener página: '.__LINE__.PHP_EOL;return false;}

	if(preg_match('/imatges\/disseny\/ko-[0-9]+\.png/msi',$data['pageContent'])){
		echo "\033[0;31mError cocinando\033[0m".PHP_EOL;
		if(isset($captcha)){
			if(file_exists('/tmp/captcha.jpg')){copy('/tmp/captcha.jpg','resources/captchas/error/'.$res.'.jpg');}
			if(file_exists('/tmp/captcha_p.png')){copy('/tmp/captcha_p.png','resources/captchas/error/'.$res.'_p.png');}
		}
		exit;
		return false;
	}

	if(!preg_match('/<div class="recurs">[^0-9]*([0-9]+)/msi',$data['pageContent'],$prize)){
		echo "\033[0;31mCookies no encontradas\033[0m".PHP_EOL;
		return false;
	}
	$GLOBALS['totalCookies'] += $prize[1];
	echo $prize[1],' | Total: ',$GLOBALS['totalCookies'].PHP_EOL;
}
