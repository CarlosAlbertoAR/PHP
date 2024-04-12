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
    private static string $method = "AES-256-CBC";
    private static int $randomStringLength = 1024;
    private static int $options = 0;
    private static string $initializationVector;
    
    private static function removeBearerString($token){
        return substr($token, 7, strlen($token));
    }

    private static function removeRandomString($token){
        return substr($token, self::$randomStringLength, strlen($token) - self::$randomStringLength);
    }

    private static function removeInitializationVector($token) {
        return substr($token, 0, openssl_cipher_iv_length(self::$method));
    }

    private static function encrypt($data)
    {
        $initializationVector = openssl_random_pseudo_bytes(openssl_cipher_iv_length(self::$method));
        $encrypted = openssl_encrypt($data, self::$method, self::$key, self::$options, $initializationVector); 
        return base64_encode($initializationVector.$encrypted);
    }

    private static function decrypt($data)
    {
        $data = self::removeRandomString($data);
        $encrypted = base64_decode($data);
        
        $initializationVector = self::removeInitializationVector($encrypted);
        $encrypted = substr($encrypted, openssl_cipher_iv_length(self::$method));    

        return openssl_decrypt($encrypted, self::$method, self::$key, 0, $initializationVector);
    }

    private static function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public static function generateToken($expiresTime)
    {
        $dataHoraValidade = date("Y-m-d H:i:s", time() + $expiresTime);
        $accessToken = self::encrypt($dataHoraValidade);
        $accessToken = self::generateRandomString(self::$randomStringLength).$accessToken;
        
        $objToken = new Token();
        $objToken->access_token = $accessToken;
        $objToken->expires_in = $expiresTime;
        $objToken->token_type ='bearer';    

        return $objToken;
    }

    public static function validToken($token)
    {
        $token = self::removeBearerString($token);
        $expires_time = new DateTime(self::decrypt($token));
        $nowDateTime = new DateTime();
        return ($nowDateTime < $expires_time);
    }
}    

?>