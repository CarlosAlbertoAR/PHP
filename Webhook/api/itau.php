<?php
require_once ("../class/itauDAO.php");
require_once ("../class/tokencontroller.php");

$json = file_get_contents('php://input');
$return = array();

http_response_code(500);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $headers = getallheaders();
    if (isset($headers['Authorization'])) {
        $authorizationHeader = $headers['Authorization'];

        if (!TokenController::isValidToken($authorizationHeader)) {
            http_response_code(403);
            die(json_encode('Erro: Token inválido ou expirado.'));
        }

    } else {
        http_response_code(400);
        die(json_encode('Erro: Cabeçalho Authorization não informado.'));
    }

    if(!empty($json)) {

        try
        {
            $objBoletos = json_decode($json, true);

            if (!isset($objBoletos)) {
                http_response_code(400);
                throw new Exception('Json inválido.');    
            }    
            
            $obj = $objBoletos['boletos'];
            $tamanho = count($obj);

            for ($i = 0; $i < $tamanho; $i++) {
                
                if (!isset($obj[$i]['idBeneficiario']))
                {
                    http_response_code(400);
                    throw new Exception('Propriedade idBeneficiario não encontrada no Json enviado.');    
                }

                if (!isset($obj[$i]['numeroNossoNumero']))
                {
                    http_response_code(400);
                    throw new Exception('Propriedade NossoNumero não encontrada no Json recebido.');    
                }
                    
                $numeroAgencia = substr($obj[$i]['idBeneficiario'], 0, 4); 
                $numeroConta = substr($obj[$i]['idBeneficiario'], 4, 8); 

                if (!ItauDAO::salvarNotificacaoItau($numeroAgencia, $numeroConta, $obj[$i]['numeroNossoNumero'], json_encode($obj[$i])))
                {
                    http_response_code(500);
                    die(json_encode("Erro: Erro interno." .PHP_EOL. "Código: 9001"));
                }
            }    

            http_response_code(200);
            echo json_encode("Sucesso: Notificação recebida.");

        } catch (Exception $e)
        {
            Log::salvarLogErro('Falha ao processar Json recebido:'.PHP_EOL.PHP_EOL .$e .PHP_EOL.PHP_EOL .$json);    
            die(json_encode($e->getMessage()));
        }

    }

} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    http_response_code(200);        
    die(json_encode('{ Webhook Banco Itaú Operacional. IP Addres: '.$_SERVER['REMOTE_ADDR'].'}'));
} else
{
    http_response_code(501);        
    die(json_encode('Método não suportado.'));
}


?>