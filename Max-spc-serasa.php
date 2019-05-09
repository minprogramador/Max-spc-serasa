<?php

require('funcs.php');
include('config.php');


$resultado = file_get_contents('debug/debug_upcredja2.json');

$resultado = json_decode($resultado, true);


$identificacao     = $resultado['identificacao'];
$endereco          = $resultado['endereco'];
$enderecoanterior  = $resultado['enderecosInformadosAnteriormente'];
$ocorrencias       = $resultado['resumoOcorrencias'];
$score             = $resultado['score'];
$score['imagem'] = '';
$consultaSpcSerasa = $resultado['consultaSpcSerasa'];


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


$res = array(
    'identificacao' => $identificacao,
    'endereco'      => $endereco,
    'endereco_anterior' => $enderecoanterior,
    'ocorrencias' => $ocorrencias,
    'score'       => $score,
    'spc_serasa'  => $consultaSpcSerasa
);


/*


pendenciasFinanceiraSerasa
pendenciasSPC
chequeSemFundo
protestos
consultaSpcSerasa


*/


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

$ocorrenc = jsonToTd2($ocorrencias);

$endAnt = jsonToTd($enderecoanterior);

//resumoOcorrencias

$consultaSpcSerasa = jsonToTd($consultaSpcSerasa);

$pendenciasFinanceiraSerasa = jsonToTd($pendenciasFinanceiraSerasa);

$protestos = jsonToTd($protestos);

$pendenciasSPC = jsonToTd($pendenciasSPC);
// echo $pendenciasFinanceiraSerasa;
// die;


$chequeSemFundo = jsonToTd($chequeSemFundo);


// print_r($resultado);
// die;



$dados = file_get_contents('layout/resultadook.html');
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
$dados = str_replace('{{ocorrenc}}', $ocorrenc, $dados);

$dados = str_replace('{{score.pontuacao}}', $score['pontuacao'], $dados);
$dados = str_replace('{{score.descricao}}', $score['descricao'], $dados);

$dados = str_replace('{{consultaSpcSerasa}}', $consultaSpcSerasa, $dados);

$dados = str_replace('{{pendenciasFinanceiraSerasa}}', $pendenciasFinanceiraSerasa, $dados);

$dados = str_replace('{{protestos}}', $protestos, $dados);

$dados = str_replace('{{pendenciasSPC}}', $pendenciasSPC, $dados);

$dados = str_replace('{{chequeSemFundo}}', $chequeSemFundo, $dados);

echo json_encode(array('dados' => $dados));
die;
print_r($res);

die;




// para CPF
$tipo = "cpf";
$documento = "340.406.926-91";
$documento = '32900637805';
// para CNPJ
/*$tipo = "cnpj";
$documento = "33.000.167/0001-01";*/

// corpo do envio, manter intacto so alterar os valores acima
$post = json_encode([
    "aba" => $tipo,
    "filtros" => [
        "campos" => [
            $tipo => [
                "documento" => $documento
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

$json = json_decode($output, TRUE);

$erro = NULL;
$resultado = NULL;

// Tratando o retorno
if($httpcode == 200){
    
    // indentificando o codigo do sistema pelo tipo
    $sistema = 10;

    // Verificando se existe o atributo data
    if(isset($json['data'])){

        // verificando se existe alguma mensagem de erro
        if(isset($json['data']['erros'][$sistema])){
            $erro = $json['data']['erros'][$sistema];
        }
        
        // Se não existir mensagem de erro, verifico se existe o retorno
        else if(isset($json['data']['consultas'][$sistema])){
            $resultado = $json['data']['consultas'][$sistema]['data'];
        }
        
        // Se não existir mensagem de erro e nem de retorno é um erro desconhecido
        else{
            $erro = "Erro desconhecido [1]";
        }

    }
    
    // Se não tiver o atributo "data" deu alguma falha no retorno
    else{
        $erro = "Erro desconhecido [2]";
    }

}
// Código != de 200 é erro
else{
    $erro = (isset($json['msg'])) ? $json['msg'] : "Erro desconhecido [3]";
}

if($erro){
    throw new Exception($erro);
}

echo json_encode($resultado);
?>
