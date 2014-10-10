<?php
$GLOBALS['config'] = array();

function html_petition($url,$data = false){
	$url = trim($url);
	// var_dump($url);
	$uinfo = parse_url(trim($url));

	$ch = curl_init();
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch,CURLOPT_FOLLOWLOCATION,0);
	curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.132 Safari/537.36');
	curl_setopt($ch,CURLOPT_HEADER,1);
	curl_setopt($ch,CURLOPT_URL,$url);
	// curl_setopt($ch,CURLOPT_VERBOSE,true);
	
	curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,2);
	curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,true);   
	

	if(isset($GLOBALS['config']['proxy']) && $GLOBALS['config']['proxy']['active'] && (!isset($data['proxy']) || $data['proxy'] !== false)){
		$proxy = $GLOBALS['config']['proxy'];
		curl_setopt($ch,CURLOPT_PROXY,$proxy['host'].':'.$proxy['port']);
		curl_setopt($ch,CURLOPT_PROXYTYPE,$proxy['type']);
		curl_setopt($ch,CURLOPT_HTTPPROXYTUNNEL,1);
	}

	if(isset($data['header'])){
		curl_setopt($ch,CURLOPT_HTTPHEADER,$data['header']);
	}

	if(isset($data['post'])){
		$postString = $data['post'];
		if(is_array($data['post'])){$postString = http_build_query($data['post']);}
		curl_setopt($ch,CURLOPT_POST,count($data['post']));
		curl_setopt($ch,CURLOPT_POSTFIELDS,$postString);
	}

	if(isset($data['requested'])){
		$h = array('X-Requested-With: '.$data['requested']);
		curl_setopt($ch,CURLOPT_HTTPHEADER,$h);
	}

	if(isset($data['headers'])){
		curl_setopt($ch,CURLOPT_HTTPHEADER,$data['headers']);
	}

	if(isset($data['files'])){
		$localFile = $data['files']['file'];
		$fp = fopen($localFile, 'r');

		//Connecting to website.
		curl_setopt($ch, CURLOPT_INFILE, $fp);
		curl_setopt($ch, CURLOPT_INFILESIZE, filesize($localFile));
	}

	if(isset($data['referer'])){
		curl_setopt($ch, CURLOPT_REFERER,$data['referer']);
	}

	if(isset($data['cookies']) && count($data['cookies']) > 0){
		$cookieData = '';foreach($data['cookies'] as $cookie){list($key,$value) = each($cookie);$cookieData .= $key.'='.$value.'; ';}
		if($cookieData == ''){$cookieData = substr($cookieData,0,-2);}
		curl_setopt($ch,CURLOPT_COOKIE,$cookieData);
	}

	// Cookie file
	if(isset($data['cookieFile'])){
		curl_setopt($ch,CURLOPT_COOKIEFILE,$data['cookieFile']['file']);
		curl_setopt($ch,CURLOPT_COOKIEJAR,$data['cookieFile']['file']);
	}

	$res = curl_exec($ch);
	if(!$res){return curl_error($ch);}

	list($header,$content) = explode("\r\n\r\n", $res, 2);
	if(strpos($header,'100 Continue') !== false){list($header,$content) = explode("\r\n\r\n", $content, 2);}

	$cookies = array();$m = preg_match_all('/Set-Cookie: (.*)/i',$header,$arr);
	if($m){foreach($arr[0] as $k=>$v){$cookie = array();$m = preg_match_all('/([a-zA-Z0-9\-_\.]*)=([^;]+)/i',$arr[1][$k],$c);foreach($c[0] as $k=>$v){$cookie[$c[1][$k]] = $c[2][$k];}$cookies[] = $cookie;}}
	if(isset($data['cookies'])){$cookies = array_merge($data['cookies'],$cookies);}
	$data['cookies'] = $cookies;

	$m = preg_match('/Location: (.*)/i',$header,$arr);
	if($m){
		$data['referer'] = trim($url);
		if(strpos($arr[1], 'http') !== false){return html_petition($arr[1],$data);}
		return html_petition($uinfo['scheme'].'://'.$uinfo['host'].'/'.$arr[1],$data);
	}

	return array('currentURL'=>$url,'pageHeader'=>$header,'pageContent'=>$content,'cookies'=>$cookies);
}

function tor_new_identity($tor_ip = '127.0.0.1',$control_port = '9051',$auth_code = '"270688"'){
	$fp = fsockopen($tor_ip,$control_port,$errno,$errstr, 30);
	if(!$fp){return false;}
	
	fputs($fp, "AUTHENTICATE $auth_code\r\n");
	$response = fread($fp,1024);

	list($code, $text) = explode(' ',$response, 2);
	if($code != '250'){return false;}
	
	fputs($fp, "signal NEWNYM\r\n");
	$response = fread($fp,1024);
	list($code, $text) = explode(' ',$response, 2);
	if($code != '250'){echo 1;return false;}
	 
	fclose($fp);
	return true;
}

function getIP(){
	$url = 'http://ifconfig.me/ip';
	$data = html_petition($url);
	$ip = $data['pageContent'];

	return trim($ip);
}
?>
