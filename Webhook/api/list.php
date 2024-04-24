<?php
require_once ("../class/DAO.php");
$con = dataBase::getConexao();

$banco = $_REQUEST["banco"];

if($banco === "bancobrasil") 
{
    $convenio = $_REQUEST["convenio"];
    
    $return = Database::retornarNotificacoesBancoBrasil($convenio);
    
    die(json_encode($return));

} else if($banco === "sicredi") 
{
    $agencia = $_REQUEST["agencia"];
    $convenio = $_REQUEST["convenio"];
    
    $return = Database::retornarNotificacoesBancoSicredi($agencia, $convenio);
    
    die(json_encode($return));

} else if($banco === "itau")
{ 
    $agencia = $_REQUEST["agencia"];
    $conta = $_REQUEST["conta"];
    
    $return = Database::retornarNotificacoesBancoItau($agencia, $conta);
    
    die(json_encode($return));

} else if($banco === "santander")
{ 
    $cnpj = $_REQUEST["cnpj"];
    $convenio = $_REQUEST["convenio"];
    
    $return = Database::retornarNotificacoesBancoSantander($cnpj, $convenio);
    
    die(json_encode($return));

} else 
{
    http_response_code(400);
    echo "Erro: Banco não suportado para consulta: ".$banco;
}

?>