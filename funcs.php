<?php

function jsonToTd($enderecoanterior) {
    $endAnt = ''; 
    foreach ($enderecoanterior as $key => $value) {
        $endAnt .= '<tr>';
        foreach($value as $v){
            if(!is_array($v)){
                $endAnt .= '<td>' . $v . '</td>';
            }else{
                $endAnt .= '<td>' . $v['date'] . '</td>';

            }
        }
        $endAnt .= '</tr>';
    }
    return $endAnt;
}

function jsonToTd2($enderecoanterior) {
    $endAnt = ''; 
    foreach ($enderecoanterior as $key => $value) {

        $endAnt .= "<tr>";
            $key = str_replace('_', ' ', $key);
            $key = ucwords($key);
            $endAnt .= '<td class="td_dark_maior">' . $key . '</td>';

        foreach($value as $v) {
            $endAnt .= '<td class="gridConsulta">' . $v . '</td>';
        }
        $endAnt .= '</tr>';
    }
    return $endAnt;
}
