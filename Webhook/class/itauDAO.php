<?php

require_once ("../class/DAO.php");
require_once ("../class/log.php");
require_once ("../class/notificacao.php");

date_default_timezone_set('America/Sao_Paulo');

class ItauDAO extends Database {

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

        $statement = DataBase::getConexao()->prepare($query);

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
                ORDER BY id                     
                   LIMIT :quantidade +1 ';
        
        $consulta = DataBase::getConexao()->prepare($query);
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
}

?>