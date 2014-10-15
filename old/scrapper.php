<?php
include_once('../inc_htmlCurl.php');

// Config data
$GLOBALS['config'] = array(
	'user'=>'',
	'pass'=>'',
	'cookie'=>'cookie.txt',
	'proxy'=>array(
		'active'=>false,
		'host'=>'127.0.0.1',
		'port'=>'9050',
		'type'=>CURLPROXY_SOCKS5,
	),
);

// Galletas totales
$GLOBALS['totalCookies'] = 0;

// Hora máxima de ejecución
$endTime = strtotime('+ '.rand(40,90).' minutes');

$ip = getIP();
echo ' # ',PHP_EOL,' # VendeCookies 2.0',PHP_EOL,' # Hora límite: ',date('H:i:s',$endTime),PHP_EOL,' # IP: ',$ip,($GLOBALS['config']['proxy']['active'] ? ' (TOR)' : ''),PHP_EOL,' # ',PHP_EOL;

// Scrapper
$data['cookieFile']['file'] = $GLOBALS['config']['cookie'];
$url = 'http://www.vendecookies.com/index.php';
$data = html_petition($url,$data);

// Login
if(preg_match('/Usuario registrado/msi',$data['pageContent'])){doLogin();}
 
getStats();

do{
	// Si llevamos más tiempo del necesario paramos
	if(time() > $endTime){break;}

	$data['cookieFile']['file'] = $GLOBALS['config']['cookie'];
	$url = 'http://www.vendecookies.com/index.php?p=cocinar';
	$data = html_petition($url,$data);

	preg_match_all('/class="ingredient.*?falta" id="ing-([0-9]+)/msi',$data['pageContent'],$m);
	foreach($m[1] as $r){
		sleep(rand(1,3));

		$data['cookieFile']['file'] = $GLOBALS['config']['cookie'];
		$url = 'http://www.vendecookies.com/index.php?p=cocinar&r='.$r;
		$data = html_petition($url,$data);
		
		if(!preg_match('/solicitar recurs-([0-9]+)/msi',$data['pageContent'],$res)){continue;}

		$data['cookieFile']['file'] = $GLOBALS['config']['cookie'];
		$data['post'] = array('resource'=>$res[1]);
		$url = 'http://www.vendecookies.com/ws/ObtainResource.php';
		$game = $data = html_petition($url,$data);
		
		sleep(rand(8,20));
		
		if(preg_match('/minijuego minijuego-001/msi',$data['pageContent'])){
			echo date('H:i:s - ').'Juego 1: ';
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
				$c = trim(shell_exec('compare -metric AE -fuzz 30% game1.jpg game1/'.$k.'.jpg diff.jpg 2>&1'));
				$diffs[$k] = $c;
				if($c == 0){break;}
			}

			asort($diffs);
			$image = key($diffs);

			copy('game1.jpg','game1/processed/'.$image.'-'.time().'.jpg');

			if($diffs[$image] > 0){
				if(strlen($diffs[$image]) > 2){
					print_r($diffs);
					copy('game1.jpg','game1/error/'.time().'.jpg');
					// exit;
				}
				else{copy('game1.jpg','game1/'.$image.'.jpg');}
			}

			if(!isset($paths[$image])){
				echo date('H:i:s - ').'Tablero no encontrado en minijuego 1: '.$image,PHP_EOL;
				continue;
			}

			preg_match('/inici-([0-9]+)"><img src="\/imatges\/minijuego\/001\/juego\/START-'.$ing[1].'\.png/msi',$data['pageContent'],$init);

			$end = $paths[$image][$init[1]];
			preg_match('/recollir-'.$end.'" onclick=\'location\.href = "([^"]+)/msi',$data['pageContent'],$win);

			$data['cookieFile']['file'] = $GLOBALS['config']['cookie'];
			$url = 'http://www.vendecookies.com/'.$win[1];
			$data = html_petition($url,$data);
		}elseif(preg_match('/minijuego minijuego-002/msi',$data['pageContent'])){
			echo date('H:i:s - ').'Juego 2: ';
			preg_match('/if\(resultat==[0-9]+\)location\.href="([^"]+)/msi',$data['pageContent'],$win);
			$data['cookieFile']['file'] = $GLOBALS['config']['cookie'];
			$url = 'http://www.vendecookies.com/'.$win[1];
			$data = html_petition($url,$data);
		}elseif(preg_match('/minijuego minijuego-003/msi',$data['pageContent'])){
			echo date('H:i:s - ').'Juego 3: ';
			preg_match('/if \(ImgFound == ImgSource\.length\) {\s*location.href = "([^"]+)/msi',$data['pageContent'],$win);
			$data['cookieFile']['file'] = $GLOBALS['config']['cookie'];
			$url = 'http://www.vendecookies.com/'.$win[1];
			$data = html_petition($url,$data);
		}elseif(preg_match('/minijuego minijuego-004/msi',$data['pageContent'])){
			echo date('H:i:s - ').'Juego 4: ';
			if(!file_exists('games/game4.html')){file_put_contents('games/game4.html',$data['pageContent']);}

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
				$c = trim(shell_exec('compare -metric AE game4.png game4/00'.$i.'.png diff.png 2>&1'));
				if($c == 0){$image = '00'.$i;break;}
			}
			copy('game4.png','game4/processed/'.$image.'-'.time().'.png');

			preg_match('/url\("\/imatges\/minijuego\/004\/([0-9]+).*?\.minijuego-004 \.bandeja-2{background:url\("\/imatges\/minijuego\/004\/([0-9]+)/msi',$data['pageContent'],$plates);

			preg_match('/base-([0-9]+).jpg/msi',$data['pageContent'],$type);
			$plate = ($cookieCount[$plates[1]][$image] < $cookieCount[$plates[2]][$image] ? 1 : 2);
			if($type[1] == 1){$plate = ($cookieCount[$plates[1]][$image] > $cookieCount[$plates[2]][$image] ? 1 : 2);}

			preg_match('/bandeja-'.$plate.'" onclick=\'location\.href = "([^"]+)/msi',$data['pageContent'],$win);
			
			$data['cookieFile']['file'] = $GLOBALS['config']['cookie'];
			$url = 'http://www.vendecookies.com/'.$win[1];
			$data = html_petition($url,$data);
		}elseif(preg_match('/minijuego minijuego-005/msi',$data['pageContent'])){
			echo date('H:i:s - ').'Juego 5: ';
			preg_match('/location.href = "([^"]+)/msi',$data['pageContent'],$win);
			$data['cookieFile']['file'] = $GLOBALS['config']['cookie'];
			$url = 'http://www.vendecookies.com/'.$win[1];
			$data = html_petition($url,$data);
		}elseif(preg_match('/minijuego minijuego-006/msi',$data['pageContent'])){
			echo date('H:i:s - ').'Juego 6: ';
			preg_match('/if\(resultado==[^\)]+\){\s*location.href = "([^"]+)/msi',$data['pageContent'],$win);
			$data['cookieFile']['file'] = $GLOBALS['config']['cookie'];
			$url = 'http://www.vendecookies.com/'.$win[1];
			$data = html_petition($url,$data);
		}elseif(preg_match('/minijuego minijuego-007/msi',$data['pageContent'])){
			echo date('H:i:s - ').'Juego 7: ';
			preg_match('/desabilitarTeclado=true;\s*location.href = "([^"]+)/msi',$data['pageContent'],$win);
			$data['cookieFile']['file'] = $GLOBALS['config']['cookie'];
			$url = 'http://www.vendecookies.com/'.$win[1];
			$data = html_petition($url,$data);
		}elseif(preg_match('/minijuego minijuego-008/msi',$data['pageContent'])){
			echo date('H:i:s - ').'Juego 8: ';
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
				$c = trim(shell_exec('compare -metric AE -fuzz 30% game8.jpg game8/'.$k.'.jpg diff.jpg 2>&1'));
				$diffs[$k] = $c;
				if($c == 0){break;}
			}

			asort($diffs);
			$image = key($diffs);

			copy('game8.jpg','game8/processed/'.$image.'-'.time().'.jpg');

			if($diffs[$image] > 0){
				if(strlen($diffs[$image]) > 2){
					print_r($diffs);
					copy('game8.jpg','game8/error/'.time().'.jpg');
					// exit;
				}
				else{copy('game8.jpg','game8/'.$image.'.jpg');}
			}

			if(isset($order[$image])){
				$plats = explode(',',$order[$image]);
				preg_match('/index\.php\?p=cocinar&r=[0-9]&h="\+hash\+"([^"]+)/msi',$data['pageContent'],$h);

				$hash = '';
				foreach($plats as $plat){
					preg_match('/id="plat-([^"]+)" class="plat plat-'.$plat.'">/msi',$data['pageContent'],$res);
					$hash .= $res[1];
				}
				$hash .= $h[1];

				$url = 'http://www.vendecookies.com/index.php?p=cocinar&r='.$r.'&h='.$hash;
				$data['cookieFile']['file'] = $GLOBALS['config']['cookie'];
				$data = html_petition($url,$data);
			}else{
				echo date('H:i:s - ').'Tablero no encontrado en minijuego 8: '.$image,PHP_EOL;
				// break 2;
			}
		}elseif(preg_match('/recollir.*?onclick="location.href = \'([^\']+)/msi',$data['pageContent'],$win)){
			echo date('H:i:s - ').'Gratis:  ';
			$data['cookieFile']['file'] = $GLOBALS['config']['cookie'];
			$url = 'http://www.vendecookies.com/'.$win[1];
			$data = html_petition($url,$data);
		}else{
			echo date('H:i:s - ').'Juego no reconocido',PHP_EOL;
			file_put_contents('gameUnknown-'.time().'.html',$game['pageContent']);
			continue 2;
		}
		
		
		if(preg_match('/<div class="recurs">Has conseguido ([^<]+)/msi',$data['pageContent'],$prize)){echo $prize[1],PHP_EOL;}
		else{
			// echo date('H:i:s - ').'Ingredientes no encontrados',PHP_EOL;
			echo 'ERROR!!',PHP_EOL;
			// exit;
		}

		sleep(rand(3,5));

		$data['cookieFile']['file'] = $GLOBALS['config']['cookie'];
		$url = 'http://www.vendecookies.com/index.php?p=cocinar';
		$data = html_petition($url,$data);
	}

	if(!count($m[1])){
		$data['cookieFile']['file'] = $GLOBALS['config']['cookie'];
		$url = 'http://www.vendecookies.com/index.php?p=cocinar&r=1';
		$data = html_petition($url,$data);


		$data['cookieFile']['file'] = $GLOBALS['config']['cookie'];
		$data['post'] = array('resource'=>1);
		$url = 'http://www.vendecookies.com/ws/ObtainResource.php';
		$data = html_petition($url,$data);

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
			$result = trim(shell_exec('gocr -p ./captchas/ -m 258 /tmp/captcha.jpg'));
			$res = preg_replace('/[^a-z0-9]+/i','',$result);
			
			if(strlen($res) < 6){
				echo date('H:i:s - ').'Captcha no resuelto',PHP_EOL;
				file_put_contents('captchas/error/'.$res.'.jpg',$im['pageContent']);
				continue;
			}

			$data['post'] = array('texto'=>$res,'enviar'=>'Recoger');
		}


		echo date('H:i:s - ').'Cookies: ';
		sleep(46);
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
			if(isset($captcha)){file_put_contents('captchas/error/'.$res.'.jpg',$im['pageContent']);}
			// exit;
		}

		if(!preg_match('/<div class="recurs">[^0-9]*([0-9]+)/msi',$data['pageContent'],$prize)){
			echo 'Cookies no encontradas',PHP_EOL;
			// exit;
		}
		$GLOBALS['totalCookies'] += $prize[1];
		// echo date('H:i:s - '),'Cookies: ',$prize[1],' | Total: ',$GLOBALS['totalCookies'],PHP_EOL;
		echo $prize[1],' | Total: ',$GLOBALS['totalCookies'],PHP_EOL;

		checkGifts();


		$data['cookieFile']['file'] = $GLOBALS['config']['cookie'];
		$url = 'http://www.vendecookies.com/index.php?p=cocinar';
		$data = html_petition($url,$data);
	}
}while(true);


function doLogin(){
	echo 'Login...',PHP_EOL;
	$data['post'] = array(
		'entrar'=>'INICIAR SESIÓN',
		'password'=>$GLOBALS['config']['pass'],
		'usuario'=>$GLOBALS['config']['user'],
	);

	$data['cookieFile']['file'] = $GLOBALS['config']['cookie'];
	$url = 'http://www.vendecookies.com/index.php';
	$data = html_petition($url,$data);
}

function checkGifts(){
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

function getStats(){
		$data['cookieFile']['file'] = $GLOBALS['config']['cookie'];
		$url = 'http://www.vendecookies.com/';
		$data = html_petition($url,$data);

		preg_match_all('/class="ingredient.*?icon-([^\.]+)[^<]+<div class="text"><strong>([^<]+)/msi',$data['pageContent'],$ing);
		foreach($ing[1] as $k=>$i){
			if($i == 'cookie'){$GLOBALS['totalCookies'] = str_replace('.','',$ing[2][$k]);}
			echo ' # '.ucfirst($i).': '.$ing[2][$k],PHP_EOL;
		}
		echo ' # ',PHP_EOL;  
}

function showStatus($done,$total,$size=30){
	static $start_time;
	if($done > $total){$start_time = '';return;}
	if(empty($start_time)){$start_time = time();}

	$now = time();
	$perc = (double)($done/$total);
	$bar = floor($perc*$size);

	$status_bar = "\r[";
	$status_bar .= str_repeat('=', $bar);
	if($bar<$size){$status_bar .= '>';$status_bar .= str_repeat(' ', $size-$bar);}
	else{$status_bar .= '=';}

	$disp = number_format($perc*100, 0);

	$status_bar.="] $disp% $done/$total";

	$rate = ($now-$start_time)/$done;
	$left = $total - $done;
	$eta = round($rate * $left, 2);

	$elapsed = $now - $start_time;
	$status_bar .= ' remaining: '.number_format($eta).' sec. elapsed: '.number_format($elapsed).' sec.';
	echo "$status_bar ";
	flush();
	if($done >= $total){echo "\n";}
}

// Se debe poner siempre un referido
function registerUser($user,$pass,$email,$referer = 'dvil88'){
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
		echo date('H:i:s - '),$m[1];
		return false;
	}
}
?>