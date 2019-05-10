<?php

function xss($data, $problem='') {
    if(!is_string($data)) {
        return $data;
    }

    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = strip_tags($data);
    if ($problem && strlen($data) == 0) {
        return ($problem);
    }
    return $data;
}

function curl($url,$cookies,$post,$referer=null,$header=1,$follow=false) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, $header);
    if ($cookies) curl_setopt($ch, CURLOPT_COOKIE, $cookies);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 5.1; rv:2.0.1) Gecko/20100101 Firefox/4.0.1');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $follow);
    if(isset($referer)){ curl_setopt($ch, CURLOPT_REFERER,$referer); }
    else{ curl_setopt($ch, CURLOPT_REFERER,$url); }
    if(strlen($post) > 5)
    {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post); 
    }
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); 
    curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30); 
    curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 20);
    $res = curl_exec( $ch);

    curl_close($ch); 
    return $res;
}


function dateConvert($date) {
	if(stristr($date, '-')){
	   $date =  date('d/m/Y', strtotime($date));
	}
	return $date;	
}

function valorConvert($str) {
	if(is_array($str)) {
		return $str;
	}

	if(is_numeric($str) && strpos($str, ".") !== false) {
		return 'R$: ' . number_format($str,2);
	}

	return $str;
}

function jsonToTd($enderecoanterior) {
	if(count($enderecoanterior) == 0) {
		return '<tr><td colspan="100%" align="center">NADA CONSTA</td></tr>';
	}
    $endAnt = ''; 
    foreach ($enderecoanterior as $key => $value) {
        $endAnt .= '<tr>';
        foreach($value as $v) {
            if(!is_array($v)){
            	$v = valorConvert($v);

                $endAnt .= '<td>' . $v . '</td>';
            }else{
            	if(array_key_exists('date', $v)) {
            		$date = dateConvert($v['date']);
            	}else{
            		die('debugar....');
            	}
                $endAnt .= '<td>' . $date . '</td>';

            }
        }
        $endAnt .= '</tr>';
    }
    return $endAnt;
}

function jsonToTd2($enderecoanterior) {
	if(count($enderecoanterior) == 0) {
		return '<tr><td colspan="100%" align="center">NADA CONSTA</td></tr>';
	}

    $endAnt = ''; 
    foreach ($enderecoanterior as $key => $value) {

        $endAnt .= "<tr>";
            $key = str_replace('_', ' ', $key);
            $key = ucwords($key);
            $endAnt .= '<td class="td_dark_maior">' . $key . '</td>';

        foreach($value as $v) {
            $v = valorConvert($v);

            @$endAnt .= '<td class="gridConsulta">' . $v . '</td>';
        }
        $endAnt .= '</tr>';
    }
    return $endAnt;
}
