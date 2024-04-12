<?php
require_once ("class/database.class.php");
$con = dataBase::getConexao();

$acao = $_REQUEST["acao"];
$return = array();

if($acao === "carregar-minhas-metas") {
    $query = "select id_meta, 
                     id_usuario, 
                     titulo, 
                     valor_total, 
                     valor_inicial, 
                     data_limite 
                from metas";

    $consulta = $con->prepare($query);
    $consulta->execute();

    while ($data = $consulta->fetch(PDO::FETCH_ASSOC)) {
        $return[] = array(
            "id"          => $data["id_meta"],
            "id_usuario"  => $data["id_usuario"],
            "titulo"      => $data["titulo"],
            "total"       => $data["valor_total"],
            "inicial"     => $data["valor_inicial"],
            "data_limite" => $data["data_limite"]
        )
    }
}

die(json_encode($return));


?>