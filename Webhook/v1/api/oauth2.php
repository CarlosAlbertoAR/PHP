<?php
require_once ("../class/utils.php");
require_once ("../class/tokencontroller.php");
require_once ("../class/logDAO.php");

$CredenciaisItau = array('LSqJj2zegg8OhkS8QBJo4KCgolMlVB0QygB', 'uy4dO3mWT6pHEptgb0bG8Y2YupZZ1BbSRkTSRA3G2cRJatykymI5FZETXQRwgkpv');

$ArrayCredenciaisAutorizadas = array($CredenciaisItau);

$urlEncodedString = file_get_contents('php://input');
$headers = getallheaders();
$IDAutenticado = '';
$return = '';

http_response_code(500);
header('Content-Type:application/json');

if (validarGrantType($urlEncodedString) and (autenticarPeloBody($urlEncodedString) or autenticarPeloHeader($headers)))
{
    $Token = TokenController::createAcessToken($IDAutenticado, 900);
    http_response_code(200);              
    die(json_encode($Token));
} else
{
    
    if(empty($urlEncodedString)) {
        $mensagem = 'Tentativa de autenticação com body da requisição vazio.';
        $detalhe = 'IP: '.$_SERVER['REMOTE_ADDR'];
        LogDAO::salvarLogErroBancoDados('000', $mensagem, $detalhe);
        http_response_code(400);
        die(JsonMessage::erro('O Body está vazio'));
    }

    if (empty($clientID) or empty($clientSecret))
    {
        http_response_code(400);
        die(JsonMessage::erro('Erro: Requisição não possui credenciais.' .PHP_EOL. 'Código: 9001'));
    }    
}

function validarGrantType($urlEncodedString)
{

    if(!empty($urlEncodedString)) {

        parse_str($urlEncodedString, $params);

        if (! isset($params)) {
            LogDAO::salvarLogErroBancoDados('000', 'Requisição inválida.', $params);
            http_response_code(400);
            die(JsonMessage::erro('Requisição inválida.'));
        }
    
        if ((empty($params['grant_type'])) or ($params['grant_type'] != 'client_credentials')) {
            $mensagem = 'grant_type inválido ou não informado.'; 
            LogDAO::salvarLogErroBancoDados('000', $mensagem, $params);
            http_response_code(400);
            die(JsonMessage::erro($mensagem));
        } else
            return true;
    }
}

function autenticarPeloBody($urlEncodedString){

    $clientID = '';
    $clientSecret = '';

    if(!empty($urlEncodedString)) {

        parse_str($urlEncodedString, $params);

        if (! isset($params)) {
            LogDAO::salvarLogErroBancoDados('000', 'Requisição inválida.', $params);
            http_response_code(500);
            die(JsonMessage::erro('Requisição inválida.'));
        }
    
        if (! empty($params['client_id']))
            $clientID = $params['client_id'];

        if (! empty($params['client_secret']))
            $clientSecret = $params['client_secret'];
    }

    return Autenticar($clientID, $clientSecret);
}

function autenticarPeloHeader($headers)
{
    
    $authorizationHeader = '';
    $clientID = '';
    $clientSecret = '';

    if (isset($headers['Authorization'])) 
    {
        $authorizationHeader = $headers['Authorization'];    

        if (!empty($authorizationHeader)) {
            $authorizationArray = explode(" ", $authorizationHeader);
            $decoded = explode(":", base64_decode($authorizationArray[1]));
            $clientID = $decoded[0];
            $clientSecret = $decoded[1];
        }
    }        
   
    return Autenticar($clientID, $clientSecret);
}

function Autenticar($cliendID, $clientSecret)
{

    if (empty($clientID) and empty($clientSecret))
        return false;    

    $arrayLogin = array($cliendID, $clientSecret);

    if (in_array($arrayLogin, $GLOBALS['ArrayCredenciaisAutorizadas'])) 
    {
        $GLOBALS['IDAutenticado'] = $cliendID;
        return true;
    } else
    {    
        $mensagem = 'Credenciais inválidas.';
        $credenciais = 'ClientID: ' + $cliendID + ' ClientSecret:' + $clientSecret;
        LogDAO::salvarLogErroBancoDados('000', $mensagem, $credenciais);

        http_response_code(401);
        die(JsonMessage::erro($mensagem));
        //return false;
    }
}

