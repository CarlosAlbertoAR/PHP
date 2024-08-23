<?php
require_once ("../class/logDAO.php");

date_default_timezone_set('America/Sao_Paulo');

class Database {
    # Variável que guarda a conexão PDO
    protected static $pdo;

    protected static $limite_notificacoes_entrega = 10;
    public static $status_recebido = "Recebido";
    public static $status_entregue = "Entregue";
    public static $status_falha_entrega = "Falha_entrega";

    private function __construct() 
    {
        $driver   = 'pgsql';
        $host     = 'localhost';
        $port     = '7777';
        $dbname   = 'WebhookBancario';
        $user     = 'postgres';
        $password = 'se7e@123';

        try{
            self::$pdo = new PDO("$driver:host=$host;port=$port;dbname=$dbname;user=$user;password=$password");

            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        } catch (PDOException $e) {
            LogDAO::salvarLogErroEmDisco($e->getMessage());            
            echo $e->getMessage();
        }
    }

    public static function getConexao() 
    {
        if (!self::$pdo) {
            new Database();
        }

        return self::$pdo;
    }


}

