<?php

class JsonMessage{

    public static function success($texto) {
        return json_encode(array('Sucesso'=>$texto));
    }

    public static function erro($texto) {
        return json_encode(array('Erro'=>$texto));
    }

    public static function warning($texto) {
        return json_encode(array('Atencao'=>$texto));
    }

}