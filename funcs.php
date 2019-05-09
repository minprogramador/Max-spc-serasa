<?php

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
