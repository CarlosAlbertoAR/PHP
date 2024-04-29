<?php

require_once ("../class/DAO.php");
require_once ("../class/log.php");
require_once ("../class/notificacao.php");

date_default_timezone_set('America/Sao_Paulo');

class BancobrasilDAO extends Database {

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
    
        $statement = DataBase::getConexao()->prepare($query);
    
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
                ORDER BY id                     
                   LIMIT :quantidade +1 ';
    
        $consulta = DataBase::getConexao()->prepare($query);
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

    public static function atualizarStatusNotificacaoBancoBrasil($listaIDs, $status) 
    {
        try {
            
            $query = 'UPDATE notificacao.bancobrasil
                         SET status = :status,
                             dataentrega = CURRENT_TIMESTAMP(0)
                       WHERE id in (:listaIDs)';

            $statement = DataBase::getConexao()->prepare($query);
            $statement->bindParam(':listaIDs', $listaIDs); 
            $statement->bindParam(':status', $status);        
            
            $statement->execute(); 

            $statement->execute();
            return true;

        } catch (PDOException $e) {
            Log::salvarLogErro($e->getMessage());
            return false;    
        }
    
    }
}

?>