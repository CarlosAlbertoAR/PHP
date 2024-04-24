<?php
require_once ("../class/log.php");
require_once ("../class/notificacao.php");

date_default_timezone_set('America/Sao_Paulo');

class Database {
    # Variável que guarda a conexão PDO
    protected static $pdo;

    private static $limite_notificacoes_entrega = 10;
    private static $status_recebido = "Recebido";
    private static $status_entregue = "Entregue";
    private static $status_falha_entrega = "Falha_entrega";

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
            Log::salvarLogErro($e->getMessage());            
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

    public static function salvarNotificacaoBancoBrasil($numeroConvenio, $nossoNumero, $json) 
    {
        $numeroBancoBrasil = '001';

        $query = 'insert into notificacao.bancobrasil (                 
                                                       datarecebimento,     
                                                       numerobanco,      
                                                       numeroconvenio,    
                                                       nossonumero,      
                                                       status,
                                                       json)             
                                                values (                   
                                                       CURRENT_TIMESTAMP(0), 
                                                       :numerobanco,     
                                                       :numeroconvenio,    
                                                       :nossonumero,     
                                                       :status,
                                                       :json             
                                                       )';
    
        $statement = dataBase::getConexao()->prepare($query);
    
        $dataAtual = date("Y-m-d H:i:s");
    
        $statement->bindParam(':numerobanco', $numeroBancoBrasil); 
        $statement->bindParam(':numeroconvenio', $numeroConvenio); 
        $statement->bindParam(':nossonumero', $nossoNumero);     
        $statement->bindParam(':status', self::$status_recebido);        
        $statement->bindParam(':json', $json); 
    
        try {
            $statement->execute();
            return true;
        } catch (PDOException $e) {
            Log::salvarLogErro($e->getMessage());
            return false;    
        }
    
    }
    
    public static function retornarNotificacoesBancoBrasil($numeroConvenio)
    {
        $query = 'SELECT id,
                         datarecebimento,
                         dataentrega,
                         numerobanco, 
                         numeroconvenio, 
                         nossonumero,
                         json 
                    FROM notificacao.BancoBrasil
                   WHERE numeroconvenio = :numeroconvenio
                     AND status in (:status_recebido, :status_falha_entrega)
                   LIMIT :quantidade +1 ';
    
        $consulta = dataBase::getConexao()->prepare($query);
        $consulta->bindParam(':numeroconvenio', $numeroConvenio, PDO::PARAM_STR);        
        $consulta->bindParam(':status_recebido', self::$status_recebido);
        $consulta->bindParam(':status_falha_entrega', self::$status_falha_entrega);
        $consulta->bindParam(':quantidade', self::$limite_notificacoes_entrega);

        $consulta->execute();
        
        $notificacao = new Notificacao();
      
        while ($registro = $consulta->fetch(PDO::FETCH_ASSOC)) 
        {
            $arrayNotificao[] = array(
                'id'              => $registro['id'],
                'datarecebimento' => $registro['datarecebimento'],
                'cnpj'            => $registro['cnpj'],
                'numerobanco'     => $registro['numerobanco'],
                'nossonumero'     => $registro['nossonumero'],
                'json'            => $registro['json']
            );

            $notificacao->quantidade++;

            if ($notificacao->quantidade == self::$limite_notificacoes_entrega)
            {
                $notificacao->contem_mais_registros = ($consulta->rowCount() > self::$limite_notificacoes_entrega);
                break;
            }                

        }

        if (isset($arrayNotificao))
            $notificacao->notificacoes = $arrayNotificao;

        return $notificacao;
    }

    public static function atualizarStatusNotificacaoBancoBrasil($arrayIds, $status) 
    {
        $query = 'UPDATE notificacao.bancobrasil
                     SET status = :status
                   WHERE id in :arrayIds';
    
        $statement = dataBase::getConexao()->prepare($query);
        $statement->bindParam(':id', $arrayIds); 
        $statement->bindParam(':status', $status);        
    
        try {
            $statement->execute();
            return true;
        } catch (PDOException $e) {
            Log::salvarLogErro($e->getMessage());
            return false;    
        }
    
    }

    public static function salvarNotificacaoSantander($cnpj, $numeroBanco, $numeroConvenio, $nossoNumero, $json) 
    {
        $numeroBancoSantander = '033';

        $query = 'insert into notificacao.santander (                 
                                                     datarecebimento,     
                                                     cnpj,             
                                                     numerobanco,      
                                                     numeroconvenio,    
                                                     nossonumero,      
                                                     status,
                                                     json)             
                                            values  (                   
                                                    CURRENT_TIMESTAMP(0), 
                                                    :cnpj,            
                                                    :numerobanco,     
                                                    :numeroconvenio,    
                                                    :nossonumero,     
                                                    :status,
                                                    :json             
                                                    )';
    
        $statement = dataBase::getConexao()->prepare($query);
    
        $dataAtual = date("Y-m-d H:i:s");
    
        $statement->bindParam(':cnpj', $cnpj); 
        $statement->bindParam(':numerobanco', $numeroBancoSantander); 
        $statement->bindParam(':numeroconvenio', $numeroConvenio); 
        $statement->bindParam(':nossonumero', $nossoNumero);     
        $statement->bindParam(':status', self::$status_recebido);        
        $statement->bindParam(':json', $json); 
    
        try {
            $statement->execute();
            return true;
        } catch (PDOException $e) {
            Log::salvarLogErro($e->getMessage());
            return false;    
        }
    
    }

    public static function retornarNotificacoesBancoSantander($cnpj, $numeroConvenio)
    {
        $query = 'SELECT id,
                         datarecebimento,
                         dataentrega,
                         cnpj,
                         numerobanco, 
                         numeroconvenio, 
                         nossonumero,
                         json 
                    FROM notificacao.santander
                   WHERE cnpj = :cnpj
                     AND numeroconvenio = :numeroconvenio
                     AND status in (:status_recebido, :status_falha_entrega)
                   LIMIT :quantidade +1 ';
    
        $consulta = dataBase::getConexao()->prepare($query);
        $consulta->bindParam(':cnpj', $cnpj, PDO::PARAM_STR);
        $consulta->bindParam(':numeroconvenio', $numeroConvenio, PDO::PARAM_STR);        
        $consulta->bindParam(':status_recebido', self::$status_recebido);
        $consulta->bindParam(':status_falha_entrega', self::$status_falha_entrega);
        $consulta->bindParam(':quantidade', self::$limite_notificacoes_entrega);

        $consulta->execute();
        
        $notificacao = new Notificacao();
      
        while ($registro = $consulta->fetch(PDO::FETCH_ASSOC)) 
        {
            $arrayNotificao[] = array(
                'id'              => $registro['id'],
                'datarecebimento' => $registro['datarecebimento'],
                'cnpj'            => $registro['cnpj'],
                'numerobanco'     => $registro['numerobanco'],
                'nossonumero'     => $registro['nossonumero'],
                'json'            => $registro['json']
            );

            $notificacao->quantidade++;

            if ($notificacao->quantidade == self::$limite_notificacoes_entrega)
            {
                $notificacao->contem_mais_registros = ($consulta->rowCount() > self::$limite_notificacoes_entrega);
                break;
            }                

        }

        if (isset($arrayNotificao))
            $notificacao->notificacoes = $arrayNotificao;

        return $notificacao;
    }

    public static function salvarNotificacaoItau($numeroAgencia, $numeroConta, $nossoNumero, $json) 
    {    
        $numeroBantoItau = '341';

        $query = 'insert into notificacao.itau (                 
                                                datarecebimento,     
                                                numeroBanco,             
                                                numeroAgencia,      
                                                numeroConta,    
                                                nossonumero,      
                                                status,
                                                json)             
                                       values  (                   
                                                CURRENT_TIMESTAMP(0), 
                                                :numeroBanco,            
                                                :numeroAgencia,     
                                                :numeroConta,    
                                                :nossonumero,     
                                                :status,
                                                :json             
                                                )';

        $statement = dataBase::getConexao()->prepare($query);

        $dataAtual = date('Y-m-d H:i:s');

        $statement->bindParam(':numeroBanco', $numeroBantoItau); 
        $statement->bindParam(':numeroAgencia', $numeroAgencia); 
        $statement->bindParam(':numeroConta', $numeroConta); 
        $statement->bindParam(':nossonumero', $nossoNumero);     
        $statement->bindParam(':status', self::$status_recebido);        
        $statement->bindParam(':json', $json); 

        try {
            $statement->execute();
            return true;
        } catch (PDOException $e) {
            Log::salvarLogErro($e->getMessage());
            return false;    
        }

    }

    public static function retornarNotificacoesBancoItau($numeroAgencia, $numeroConta)
    {
        $query = 'SELECT id,
                         datarecebimento,
                         dataentrega,
                         numerobanco, 
                         numeroagencia, 
                         numeroconta, 
                         nossonumero,
                         json 
                    FROM notificacao.itau
                   WHERE numeroagencia = :numeroagencia
                     AND numeroconta = :numeroconta
                     AND status in (:status_recebido, :status_falha_entrega)
                   LIMIT :quantidade +1 ';
        
        $consulta = dataBase::getConexao()->prepare($query);
        $consulta->bindParam(':numeroagencia', $numeroAgencia, PDO::PARAM_STR);
        $consulta->bindParam(':numeroconta', $numeroConta, PDO::PARAM_STR);        
        $consulta->bindParam(':status_recebido', self::$status_recebido);
        $consulta->bindParam(':status_falha_entrega', self::$status_falha_entrega);
        $consulta->bindParam(':quantidade', self::$limite_notificacoes_entrega);
 
        $consulta->execute();
            
        $notificacao = new Notificacao();
         
        while ($registro = $consulta->fetch(PDO::FETCH_ASSOC)) 
        {
            $arrayNotificao[] = array(
                 'id'              => $registro['id'],
                 'datarecebimento' => $registro['datarecebimento'],
                 'numeroagencia'   => $registro['numeroagencia'],
                 'numeroconta'     => $registro['numeroconta'],
                'nossonumero'     => $registro['nossonumero'],
                'json'            => $registro['json']
            );
    
            $notificacao->quantidade++;

            if ($notificacao->quantidade == self::$limite_notificacoes_entrega)
            {
                $notificacao->contem_mais_registros = ($consulta->rowCount() > self::$limite_notificacoes_entrega);
                break;
            }                
        }

        if (isset($arrayNotificao))
            $notificacao->notificacoes = $arrayNotificao;

        return $notificacao;
    }

    public static function salvarNotificacaoSicredi($numeroAgencia, $numeroConvenio, $nossoNumero, $json) 
    {
        $NumeroBancoSiredi = '748';

        $query = 'insert into notificacao.sicredi  (                 
                                                     numeroagencia,
                                                     datarecebimento,     
                                                     numerobanco,      
                                                     numeroconvenio,    
                                                     nossonumero,
                                                     status,      
                                                     json)             
                                            values  (                   
                                                     :numeroagencia,
                                                     CURRENT_TIMESTAMP(0), 
                                                     :numerobanco,     
                                                     :numeroconvenio,    
                                                     :nossonumero,
                                                     :status,     
                                                     :json             
                                                    )';
    
        $statement = dataBase::getConexao()->prepare($query);
    
        $dataAtual = date('Y-m-d H:i:s');
        $numeroConvenioSemZeroEsquerda = intval($numeroConvenio);
        $numeroConvenio = strval($numeroConvenioSemZeroEsquerda);
    
        $statement->bindParam(':numeroagencia', $numeroAgencia); 
        $statement->bindParam(':numerobanco', $NumeroBancoSiredi); 
        $statement->bindParam(':numeroconvenio', $numeroConvenio); 
        $statement->bindParam(':nossonumero', $nossoNumero);
        $statement->bindParam(':status', self::$status_recebido);
        $statement->bindParam(':json', $json); 
    
        try {
            $statement->execute();
            return true;
        } catch (PDOException $e) {
            Log::salvarLogErro($e->getMessage());
            return false;    
        }
    
    }

    public static function retornarNotificacoesBancoSicredi($numeroAgencia, $numeroConvenio)
    {
        $query = 'SELECT id,
                         datarecebimento,
                         dataentrega,
                         numerobanco, 
                         numeroconvenio, 
                         numeroagencia, 
                         nossonumero,
                         json 
                    FROM notificacao.sicredi
                   WHERE numeroagencia = :numeroagencia
                     AND numeroconvenio = :numeroconvenio
                     AND status in (:status_recebido, :status_falha_entrega)
                   LIMIT :quantidade +1 ';
    
        $consulta = dataBase::getConexao()->prepare($query);
        $consulta->bindParam(':numeroagencia', $numeroAgencia, PDO::PARAM_STR);
        $consulta->bindParam(':numeroconvenio', $numeroConvenio, PDO::PARAM_STR);        
        $consulta->bindParam(':status_recebido', self::$status_recebido);
        $consulta->bindParam(':status_falha_entrega', self::$status_falha_entrega);
        $consulta->bindParam(':quantidade', self::$limite_notificacoes_entrega);

        $consulta->execute();
        
        $notificacao = new Notificacao();
      
        while ($registro = $consulta->fetch(PDO::FETCH_ASSOC)) 
        {
            $arrayNotificao[] = array(
                'id'              => $registro['id'],
                'datarecebimento' => $registro['datarecebimento'],
                'numerobanco'     => $registro['numerobanco'],
                'numeroagencia'   => $registro['numeroagencia'],
                'nossonumero'     => $registro['nossonumero'],
                'json'            => $registro['json']
            );

            $notificacao->quantidade++;

            if ($notificacao->quantidade == self::$limite_notificacoes_entrega)
            {
                $notificacao->contem_mais_registros = ($consulta->rowCount() > self::$limite_notificacoes_entrega);
                break;
            }                

        }

        if (isset($arrayNotificao))
            $notificacao->notificacoes = $arrayNotificao;

        return $notificacao;
    }


}

?>