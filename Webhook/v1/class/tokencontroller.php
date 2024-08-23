<?php

date_default_timezone_set('America/Sao_Paulo');

class Token 
{
    public string $access_token;
    public string $token_type;
    public int $expires_in;
}
class TokenController
{
 
    private static string $key = "se7e@sistemas.ltda";    
    private static string $algorithm = 'sha256';
    
    private static function getHeader()
    {
        return json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
    }
    
    private static function encodeBase64(string $string): string
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($string));
    }

    private static function removeBearerString($token){
        return substr($token, 7, strlen($token));
    }
    
    public static function createAcessToken($clientID, $secondsToExpires)
    {
        
        $expiration_time = date("Y-m-d H:i:s", time() + $secondsToExpires);
        $signatureHash = 
        $payload = json_encode(['user_id' => $clientID, 'expiration_time' => $expiration_time]);

        $base64UrlHeader = self::encodeBase64(self::getHeader());
        $base64UrlPayload = self::encodeBase64($payload);

        $signature = hash_hmac(self::$algorithm, $base64UrlHeader . "." . $base64UrlPayload, self::$key, true);        

        $base64UrlSignature = self::encodeBase64($signature);
        $jwt = $base64UrlHeader. "." . $base64UrlPayload . "." . $base64UrlSignature;

        $objToken = new Token();
        $objToken->access_token = $jwt;
        $objToken->expires_in = $secondsToExpires;
        $objToken->token_type ='bearer';    

        return $objToken;
    }

    public static function isValidToken($token)
    {
        $token = self::removeBearerString($token);
        list($base64Header, $base64Payload, $base64Signature) = explode (".", $token);
        $signature = hash_hmac(self::$algorithm, $base64Header . "." . $base64Payload, self::$key, true);        
        
        if (self::encodeBase64($signature) != $base64Signature)
            return false;                        

        $payload = base64_decode($base64Payload);
        $jsonObject = json_decode($payload);
        
        $expiresTime = new DateTime($jsonObject->{'expiration_time'});
        $nowDateTime = new DateTime();
        
        return ($nowDateTime < $expiresTime);
    }
}    

?>