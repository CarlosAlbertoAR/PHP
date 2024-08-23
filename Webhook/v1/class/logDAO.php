<?php
require_once ("../class/DAO.php");

class LogDAO extends Database {

    public static function salvarLogErroBancoDados($numeroBanco, $descricao, $detalhe) 
    {    
        $query = 'insert into log.erro (                 
                                        data,
                                        numeroBanco,
                                        descricao,             
                                        detalhe)             
                                values  (                   
                                        CURRENT_TIMESTAMP(0), 
                                        :numeroBanco,
                                        :descricao,
                                        :detalhe             
                                        )';

        $statement = DataBase::getConexao()->prepare($query);

        $statement->bindParam(':numeroBanco', $numeroBanco); 
        $statement->bindParam(':descricao', $descricao); 
        $statement->bindParam(':detalhe', $detalhe); 

        try {
            $statement->execute();
            return true;
        } catch (PDOException $e) {
            $texto = 'Data' + date('Y-m-d H:i:s') .PHP_EOL.PHP_EOL + 
                     'Banco' + $numeroBanco .PHP_EOL.PHP_EOL + 
                     'Descricao ' + $descricao .PHP_EOL.PHP_EOL + 
                     'Detalhe ' + $detalhe .PHP_EOL.PHP_EOL.PHP_EOL.PHP_EOL + 
                     'Erro PDO ' .PHP_EOL.PHP_EOL + $e->getMessage();
                     
            self::salvarLogErroEmDisco($texto);
            return false;    
        }

    }

    public static function salvarLogErroEmDisco($texto) {
        $nomeArquivo = 'erro_'.date('d_m__Y_H_i_s').'.log';
        return file_put_contents($nomeArquivo, $texto);                
    }

}

