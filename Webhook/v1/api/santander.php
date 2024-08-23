<?php
require_once ("../class/utils.php");
require_once ("../class/santanderDAO.php");
require_once ("../class/logDAO.php");

$json = file_get_contents('php://input');
$return = array();

http_response_code(500);
header('Content-Type:application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if(!empty($json)) {

        try
        {
            $obj = json_decode($json);

            if (!isset($obj)) {
                http_response_code(400);
                throw new Exception('Json inválido.');    
            }
            
            if (!isset($obj->{'payerDocumentNumber'}))
            {
                http_response_code(400);
                throw new Exception('Propriedade payerDocumentNumber não encontrada no Json recebido.');    
            }

            if (!isset($obj->{'bankCode'}))
            {
                http_response_code(400);
                throw new Exception('Propriedade bankCode não encontrada no Json recebido.');    
            }

            if (!isset($obj->{'covenant'}))
            {
                http_response_code(400);
                throw new Exception('Propriedade covenant não encontrada no Json recebido.');    
            }

            if (!isset($obj->{'bankNumber'}))
            {
                http_response_code(400);
                throw new Exception('Propriedade bankNumber não encontrada no Json recebido.');    
            }

            if (SantanderDAO::salvarNotificacaoSantander($obj->{'payerDocumentNumber'}, $obj->{'bankCode'}, $obj->{'covenant'}, $obj->{'bankNumber'}, $json)){
                http_response_code(200);
                echo JsonMessage::success('Notificação recebida.');
            } else {
                http_response_code(500);
                die(JsonMessage::erro('Erro interno.' .PHP_EOL. 'Código: 9001'));
            }

        } catch (Exception $e)
        {
            LogDAO::salvarLogErroBancoDados(BANCO_BRASIL, 'Falha ao processar Json recebido', $e .PHP_EOL.PHP_EOL .$json);                
            die(JsonMessage::erro($e->getMessage()));
        }

    } else {
        $jsonCadastro = '{Operacao: Cadastro Webhook} IP Addres: '.$_SERVER['REMOTE_ADDR'];
        
        if (SantanderDAO::salvarNotificacaoSantander('', '', '', '', $jsonCadastro)){
            http_response_code(200);
            echo JsonMessage::success('Teste Webhook ok!.');
        } else {
            http_response_code(500);
            die(JsonMessage::erro('Erro interno.' .PHP_EOL. 'Código: 9002'));
        }
    
    }

} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    http_response_code(200);        
    die(JsonMessage::success('Webhook Banco do Santander Operacional. IP Addres: '.$_SERVER['REMOTE_ADDR']));
} else
{
    http_response_code(501);        
    die(JsonMessage::erro('Método não suportado.'));
}    

