<?php

require_once ("../class/DAO.php");
require_once ("../class/log.php");
require_once ("../class/notificacao.php");

date_default_timezone_set('America/Sao_Paulo');

class SantanderDAO extends Database {

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
    
        $statement = DataBase::getConexao()->prepare($query);
    
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
                ORDER BY id                     
                   LIMIT :quantidade +1 ';
    
        $consulta = DataBase::getConexao()->prepare($query);
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
}

?>