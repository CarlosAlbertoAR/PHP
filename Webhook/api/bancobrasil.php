<?php

require_once ("../class/utils.php");
require_once ("../class/bancobrasilDAO.php");

$jsonArray = file_get_contents('php://input');
$return = array();

http_response_code(500);
header('Content-Type:application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if(!empty($jsonArray)) {

        try
        {
            $obj = json_decode($jsonArray, True);

            if (! isset($obj)) {
                http_response_code(400);
                throw new Exception('Json inválido.');    
            }    
            
            $tamanho = count($obj);
            
            for ($i = 0; $i < $tamanho; $i++) {

                if (!isset($obj[$i]['numeroConvenio']))
                {
                    http_response_code(400);
                    throw new Exception('Propriedade numeroConvenio não encontrada no Json enviado.');    
                }

                if (!isset($obj[$i]['id']))
                {
                    http_response_code(400);
                    throw new Exception('Propriedade id não encontrada no Json recebido.');    
                }
                
                if (!BancobrasilDAO::salvarNotificacaoBancoBrasil($obj[$i]['numeroConvenio'], $obj[$i]['id'], json_encode($obj[$i]))){
                    http_response_code(500);
                    die(JsonMessage::erro('Erro interno.' .PHP_EOL. 'Código: 9001'));
                }

            }    

            http_response_code(200);
            echo JsonMessage::success('Notificação recebida.');

        } catch (Exception $e)
        {
            Log::salvarLogErro('Falha ao processar Json recebido:'.PHP_EOL.PHP_EOL .$e .PHP_EOL.PHP_EOL .$jsonArray);    
            die(JsonMessage::erro($e->getMessage()));
        }             

    } else {
        $jsonCadastro = '{Operacao: teste Webhook} IP Addres: '.$_SERVER['REMOTE_ADDR'];
        
        if (BancobrasilDAO::salvarNotificacaoBancoBrasil('', '', $jsonCadastro)){
            http_response_code(200);
            echo JsonMessage::success('Teste Webhook ok!.');
        } else {
            http_response_code(500);
            die(JsonMessage::erro('Erro interno.' .PHP_EOL. 'Código: 9002'));
        }
    
    }

} else if ($_SERVER['REQUEST_METHOD'] === 'PATCH') {
    
    if(!empty($jsonArray)) {

        try
        {   $listaIDs = "";
            $array = json_decode($jsonArray, true); 
            $tamanho = sizeof($array);
            
            for ($i = 0; $i < $tamanho; $i++)
                $listaIDs = $listaIDs.', '.$array[$i];

            
            $listaIDs = substr($listaIDs, 1);

            if (!BancobrasilDAO::atualizarStatusNotificacaoBancoBrasil($listaIDs, BancobrasilDAO::$status_entregue)){
                http_response_code(500);
                die(json_encode("Erro: Erro interno." .PHP_EOL. "Código: 9001"));
            }

            http_response_code(200);
            echo JsonMessage::success('Notificação recebida.');

        } catch (Exception $e)
        {
            Log::salvarLogErro('Falha ao processar Json recebido:'.PHP_EOL.PHP_EOL .$e .PHP_EOL.PHP_EOL .$jsonArray);    
            die(json_encode($e->getMessage()));
        }             
    }        

} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    http_response_code(200);        
    die(JsonMessage::success('Webhook Banco do Brasil Operacional. IP Addres: '.$_SERVER['REMOTE_ADDR']));
} else
{
    http_response_code(501);        
    die(json_encode('Método não suportado.'));
}

