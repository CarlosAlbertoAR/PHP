<?php
require_once ("../class/sicrediDAO.php");

$json = file_get_contents('php://input');
$return = array();

http_response_code(500);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if(!empty($json)) {

        try
        {
            $obj = json_decode($json);

            if (! isset($obj)) {
                http_response_code(400);
                throw new Exception('Json inválido.');    
            }
            
            if (!isset($obj->{'agencia'}))
            {
                http_response_code(400);
                throw new Exception('Propriedade agencia não encontrada no Json recebido.');    
            }

            if (!isset($obj->{'beneficiario'}))
            {
                http_response_code(400);
                throw new Exception('Propriedade beneficiario não encontrada no Json recebido.');    
            }

            if (!isset($obj->{'nossoNumero'}))
            {
                http_response_code(400);
                throw new Exception('Propriedade nossoNumero não encontrada no Json recebido.');    
            }
            
            if (SicrediDAO::salvarNotificacaoSicredi($obj->{'agencia'}, $obj->{'beneficiario'}, $obj->{'nossoNumero'}, $json)){
                http_response_code(200);
                echo json_encode("Sucesso: Notificação recebida.");
            } else {
                http_response_code(500);
                die(json_encode("Erro: Erro interno." .PHP_EOL. "Código: 9001"));
            }
        
        } catch (Exception $e)
        {
            Log::salvarLogErro('Falha ao processar Json recebido:'.PHP_EOL.PHP_EOL .$e .PHP_EOL.PHP_EOL .$json);    
            die(json_encode($e->getMessage()));
        }                    

    } else {
        $jsonCadastro = '{Operacao: Cadastro Webhook} IP Addres: '.$_SERVER['REMOTE_ADDR'];
        
        if (SicrediDAO::salvarNotificacaoSicredi('', '', '', $jsonCadastro)){
            http_response_code(200);
            echo json_encode("Sucesso: Teste Webhook ok!.");
        } else {
            http_response_code(500);
            die(json_encode("Erro: Erro interno." .PHP_EOL. "Código: 9002"));
        }
    
    }

} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    http_response_code(200);        
    die(json_encode('{ Webhook Banco Sicredi Operacional. IP Addres: '.$_SERVER['REMOTE_ADDR'].'}'));
} else
{
    http_response_code(501);        
    die(json_encode('Método não suportado.'));
}

?>