<?php
date_default_timezone_set('America/Sao_Paulo');
require_once ("../class/log.php");

class Database {
    # Variável que guarda a conexão PDO
    protected static $db;

    private function __construct() {
        $driver   = 'pgsql';
        $host     = 'localhost';
        $port     = '7777';
        $dbname   = 'WebhookBancario';
        $user     = 'postgres';
        $password = 'se7e@123';

        try{
            self::$db = new PDO("$driver:host=$host;port=$port;dbname=$dbname;user=$user;password=$password");

            self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        } catch (PDOException $e) {
            Log::salvarLogErro($e->getMessage());            
            echo $e->getMessage();
        }
    }

    public static function getConexao() {
        if (!self::$db) {
            new Database();
        }

        return self::$db;
    }

    public static function salvarNotificacaoSantander($cnpj, $numeroBanco, $numeroConvenio, $nossoNumero, $json) {
        $numeroBancoSantander = '033';

        $query = 'insert into notificacao.santander (                 
                                                     datarecebimento,     
                                                     cnpj,             
                                                     numerobanco,      
                                                     numeroconvenio,    
                                                     nossonumero,      
                                                     json)             
                                            values  (                   
                                                    CURRENT_TIMESTAMP(0), 
                                                    :cnpj,            
                                                    :numerobanco,     
                                                    :numeroconvenio,    
                                                    :nossonumero,     
                                                    :json             
                                                    )';
    
        $statement = dataBase::getConexao()->prepare($query);
    
        $dataAtual = date("Y-m-d H:i:s");
    
        $statement->bindParam(':cnpj', $cnpj); 
        $statement->bindParam(':numerobanco', $numeroBancoSantander); 
        $statement->bindParam(':numeroconvenio', $numeroConvenio); 
        $statement->bindParam(':nossonumero', $nossoNumero);     
        $statement->bindParam(':json', $json); 
    
        try {
            $statement->execute();
            return true;
        } catch (PDOException $e) {
            Log::salvarLogErro($e->getMessage());
            return false;    
        }
    
    }

    public static function salvarNotificacaoItau($numeroAgencia, $numeroConta, $nossoNumero, $json) {    
        $numeroBantoItau = '341';

        $query = 'insert into notificacao.itau (                 
                                                datarecebimento,     
                                                numeroBanco,             
                                                numeroAgencia,      
                                                numeroConta,    
                                                nossonumero,      
                                                json)             
                                       values  (                   
                                                CURRENT_TIMESTAMP(0), 
                                                :numeroBanco,            
                                                :numeroAgencia,     
                                                :numeroConta,    
                                                :nossonumero,     
                                                :json             
                                                )';

        $statement = dataBase::getConexao()->prepare($query);

        $dataAtual = date("Y-m-d H:i:s");

        $statement->bindParam(':numeroBanco', $numeroBantoItau); 
        $statement->bindParam(':numeroAgencia', $numeroAgencia); 
        $statement->bindParam(':numeroConta', $numeroConta); 
        $statement->bindParam(':nossonumero', $nossoNumero);     
        $statement->bindParam(':json', $json); 

        try {
            $statement->execute();
            return true;
        } catch (PDOException $e) {
            Log::salvarLogErro($e->getMessage());
            return false;    
        }

    }

    public static function salvarNotificacaoSicredi($numeroAgencia, $numeroConvenio, $nossoNumero, $json) {
        $NumeroBancoSiredi = '748';

        $query = 'insert into notificacao.sicredi  (                 
                                                     numeroagencia,
                                                     datarecebimento,     
                                                     numerobanco,      
                                                     numeroconvenio,    
                                                     nossonumero,      
                                                     json)             
                                            values  (                   
                                                     :numeroagencia,
                                                     CURRENT_TIMESTAMP(0), 
                                                     :numerobanco,     
                                                     :numeroconvenio,    
                                                     :nossonumero,     
                                                     :json             
                                                    )';
    
        $statement = dataBase::getConexao()->prepare($query);
    
        $dataAtual = date("Y-m-d H:i:s");
    
        $statement->bindParam(':numeroagencia', $numeroAgencia); 
        $statement->bindParam(':numerobanco', $NumeroBancoSiredi); 
        $statement->bindParam(':numeroconvenio', $numeroConvenio); 
        $statement->bindParam(':nossonumero', $nossoNumero);     
        $statement->bindParam(':json', $json); 
    
        try {
            $statement->execute();
            return true;
        } catch (PDOException $e) {
            Log::salvarLogErro($e->getMessage());
            return false;    
        }
    
    }

    public static function salvarNotificacaoBancoBrasil($numeroConvenio, $nossoNumero, $json) {
        $numeroBancoBrasil = '001';

        $query = 'insert into notificacao.bancobrasil (                 
                                                       datarecebimento,     
                                                       numerobanco,      
                                                       numeroconvenio,    
                                                       nossonumero,      
                                                       json)             
                                                values (                   
                                                       CURRENT_TIMESTAMP(0), 
                                                       :numerobanco,     
                                                       :numeroconvenio,    
                                                       :nossonumero,     
                                                       :json             
                                                       )';
    
        $statement = dataBase::getConexao()->prepare($query);
    
        $dataAtual = date("Y-m-d H:i:s");
    
        $statement->bindParam(':numerobanco', $numeroBancoBrasil); 
        $statement->bindParam(':numeroconvenio', $numeroConvenio); 
        $statement->bindParam(':nossonumero', $nossoNumero);     
        $statement->bindParam(':json', $json); 
    
        try {
            $statement->execute();
            return true;
        } catch (PDOException $e) {
            Log::salvarLogErro($e->getMessage());
            return false;    
        }
    
    }

}

?>