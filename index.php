<?php

require('funcs.php');
include('config.php');

function saveLog($doc, $res) {
    if(file_put_contents('./debug/'.$doc.'.log', $res)) {
        return true;
    }
    return false;
}

function consultar($doc, $urltoken, $token) {

    @$resultado = file_get_contents('debug/32900637805.log');

    if(stristr($resultado, '{')) {
        $resultado = json_decode($resultado, true);
        $resultado = $resultado['data']['consultas'][10]['data'];
        return $resultado;
    }
    return $resultado;

    /*========================================*/

    if(!preg_match("#^([0-9]){3}([0-9]){3}([0-9]){3}([0-9]){2}$#i", $doc)){
        return 'doc_invalido';
    }
    $tipo = "cpf";

    $post = json_encode([
        "aba" => $tipo,
        "filtros" => [
            "campos" => [
                $tipo => [
                    "documento" => $doc
                ]
            ],
            "empresas" => [
                10 => true
            ],
            "opcionais" => []
        ],
        "sistema" => 2
    ]);

    $header = [
        'Content-Type: application/json; charset=utf-8',
        'Content-Length: ' . strlen($post),
        'Authorization: Bearer '.$token,
        'fingerprint: API'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $urltoken); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

    $output = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    saveLog($doc, $output);

    if(!stristr($output, 'identificacao')) {
        return 'indisponivel';
    }

    $json = json_decode($output, TRUE);
    $erro = NULL;
    $resultado = NULL;

    if($httpcode == 200){
        $sistema = 10;
        if(isset($json['data'])) {
            if(isset($json['data']['erros'][$sistema])) {
                $erro = $json['data']['erros'][$sistema];
            }else if(isset($json['data']['consultas'][$sistema])){
                $resultado = $json['data']['consultas'][$sistema]['data'];
            }else{
                $erro = "Erro desconhecido [1]";
            }
        }else{
            $erro = "Erro desconhecido [2]";
        }
    }else{
        $erro = (isset($json['msg'])) ? $json['msg'] : "Erro desconhecido [3]";
    }

    if($erro) {
        return 'erro_desconhecido';
    }

    return $resultado;
}

function filtrar($resultado) {
    if(!is_array($resultado)) {
        return false;
    }

    if(array_key_exists('identificacao', $resultado)) {
        $identificacao     = $resultado['identificacao'];
        $endereco          = $resultado['endereco'];
        $enderecoanterior  = $resultado['enderecosInformadosAnteriormente'];
    }else{
        $identificacao = false;
    }

    if(array_key_exists('resumoOcorrencias', $resultado)) {
        $ocorrencias = $resultado['resumoOcorrencias'];
    }else{
        $ocorrencias = false;
    }

    if(array_key_exists('score', $resultado)) {
        $score = $resultado['score'];
    }else{
        $score = false;
    }


    if(array_key_exists('consultaSpcSerasa', $resultado)) {
        $consultaSpcSerasa = $resultado['consultaSpcSerasa'];
    }else{
        $consultaSpcSerasa = false;
    }

    if(array_key_exists('protestos', $resultado)) {
        $protestos = $resultado['protestos'];
    }else{
        $protestos = array();
    }

    if(array_key_exists('pendenciasSPC', $resultado)) {
        $pendenciasSPC = $resultado['pendenciasSPC'];
    }else{
        $pendenciasSPC = array();
    }

    if(array_key_exists('pendenciasFinanceiraSerasa', $resultado)) {
        $pendenciasFinanceiraSerasa = $resultado['pendenciasFinanceiraSerasa'];
    }else{
        $pendenciasFinanceiraSerasa = array();
    }

    if(array_key_exists('chequeSemFundo', $resultado)) {
        $chequeSemFundo = $resultado['chequeSemFundo'];
    }else{
        $chequeSemFundo = array();
    }

    $cpf = $identificacao['cpf'];
    if(array_key_exists('situacao_do_cpf', $identificacao)) {
        $situacao_do_cpf = $identificacao['situacao_do_cpf'];
    }else{
        $situacao_do_cpf = '';
    }

    if(array_key_exists('data_da_inscricao_do_cpf', $identificacao)) {
        $data_da_inscricao_do_cpf = $identificacao['data_da_inscricao_do_cpf'];
    }else{
        $data_da_inscricao_do_cpf = '';
    }

    $nome = $identificacao['nome'];
    if(array_key_exists('data_de_nascimento', $identificacao)) {
        $data_de_nascimento = $identificacao['data_de_nascimento']['date'];
        if(stristr($data_de_nascimento, '-')){
           $data_de_nascimento =  date('d/m/Y', strtotime($data_de_nascimento));
        }
    }else{
        $data_de_nascimento = '';
    }

    $nome_da_mae = $identificacao['nome_da_mae'];

    $endereco = $resultado['endereco']['endereco'];
    $bairro   = $resultado['endereco']['bairro'];
    $cidade   = $resultado['endereco']['cidade'];
    $uf       = $resultado['endereco']['uf'];
    $cep      = $resultado['endereco']['cep'];
    if(is_array($score) && array_key_exists('pontuacao', $score)) {
        $scorepontos = $score['pontuacao'];
    }else{
        $scorepontos = '-';
    }

    $ocorrenc       = jsonToTd2($ocorrencias);
    $endAnt         = jsonToTd($enderecoanterior);
    $protestos      = jsonToTd($protestos);
    $pendenciasSPC  = jsonToTd($pendenciasSPC);
    $chequeSemFundo = jsonToTd($chequeSemFundo);
    $consultaSpcSerasa = jsonToTd($consultaSpcSerasa);
    $pendenciasFinanceiraSerasa = jsonToTd($pendenciasFinanceiraSerasa);

    if($data_da_inscricao_do_cpf != '') {
        $data_da_inscricao_do_cpf = '&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp;Data da Inscrição do CPF:&nbsp;<span id="filtro_cpf_span">'.$data_da_inscricao_do_cpf.'</span>';
    }
    if($situacao_do_cpf != '') {
        $situacao_do_cpf = '&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp;Situação do CPF: <span id="filtro_cpf_span">'.$situacao_do_cpf.'</span>';
    }

    $dados = file_get_contents('tpls/Max-spc-serasa/resultadook.html');

    $dados = str_replace('{{cpf}}', $cpf, $dados);
    $dados = str_replace('{{situacao_do_cpf}}', $situacao_do_cpf, $dados);
    $dados = str_replace('{{data_da_inscricao_do_cpf}}', $data_da_inscricao_do_cpf, $dados);
    $dados = str_replace('{{nome}}', $nome, $dados);
    $dados = str_replace('{{data_de_nascimento}}', $data_de_nascimento, $dados);
    $dados = str_replace('{{nome_da_mae}}', $nome_da_mae, $dados);
    $dados = str_replace('{{endereco}}', $endereco, $dados);
    $dados = str_replace('{{bairro}}', $bairro, $dados);
    $dados = str_replace('{{cidade}}', $cidade, $dados);
    $dados = str_replace('{{uf}}', $uf, $dados);
    $dados = str_replace('{{cep}}', $cep, $dados);
    $dados = str_replace('{{endereco_anterior}}', $endAnt, $dados);
    $dados = str_replace('{{score.pontuacao}}', $scorepontos, $dados);
    $dados = str_replace('{{score.descricao}}', $score['descricao'], $dados);
    $dados = str_replace('{{ocorrenc}}', $ocorrenc, $dados);
    $dados = str_replace('{{consultaSpcSerasa}}', $consultaSpcSerasa, $dados);
    $dados = str_replace('{{pendenciasFinanceiraSerasa}}', $pendenciasFinanceiraSerasa, $dados);
    $dados = str_replace('{{protestos}}', $protestos, $dados);
    $dados = str_replace('{{pendenciasSPC}}', $pendenciasSPC, $dados);
    $dados = str_replace('{{chequeSemFundo}}', $chequeSemFundo, $dados);

    return json_encode(array('dados' => $dados));
}

if(isset($_POST['dados'])) {

    // $dados = array('msg' => 'nadaencontrado');
    // $dados = array('msg' => 'reload');
    // $dados = array('msg' => 'fail');
    // $dados = array('msg' => 'invalido');

    $documento = $_POST['dados'];
    $documento = str_replace(array('.', ',', '-', '/', ' ', '_', "\t", "\n", "\r"), '', $documento);
    $resultado = consultar($documento, $urltoken, $token);
    if($resultado == 'erro_desconhecido') {
        $dados = array('msg' => 'fail');
        echo json_encode($dados);
        die;
    }

    $dados = filtrar($resultado);

    if($dados !== false) {
        echo $dados;
    }else{
        $dados = array('msg' => 'nadaencontrado');
        echo json_encode($dados);
    }
    die;
}else{
    $tpl = file_get_contents('tpls/Max-spc-serasa/index.html');
    $tpl = str_replace(array("\n", "\r", "\t", "  "), '', $tpl);
    echo $tpl;
}

die;
