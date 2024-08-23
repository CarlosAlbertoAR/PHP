<?php
class Notificacao
{
    public int $quantidade;
    public bool $contem_mais_registros;
    public array $notificacoes;

    function __construct()
    {
        $this->quantidade = 0;
        $this->contem_mais_registros = false;
        $this->notificacoes = [];
    }
}

?>