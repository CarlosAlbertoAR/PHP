<?php

require_once ("../class/constantes.php");
require_once ("../class/DAO.php");
require_once ("../class/logDAO.php");
require_once ("../class/notificacao.php");

date_default_timezone_set('America/Sao_Paulo');

class SicrediDAO extends Database {

    public static function salvarNotificacaoSicredi($numeroAgencia, $numeroConvenio, $nossoNumero, $json) 
    {
        $NumeroBancoSiredi = BANCO_SICREDI;

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
            LogDAO::salvarLogErroEmDisco($e->getMessage());
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
                ORDER BY id
                     LIMIT :quantidade +1';
    
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
