<?php

class Log{

    public static function salvarLogErro($texto) {
        $nomeArquivo = 'erro_'.date('d_m__Y_H_i_s').'.log';
        return file_put_contents($nomeArquivo, $texto);                
    }

}

?>