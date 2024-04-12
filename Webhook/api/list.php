<?php
require_once ("../class/dao.class.php");
$con = dataBase::getConexao();

$banco = $_REQUEST["banco"];
$return = array();

if($banco === "sicredi") 
{
    $query = "select id,
                     datarecebimento,
                     dataentrega,
                     numerobanco, 
                     numeroagencia, 
                     nossonumero,
                     json 
                from notificacao.sicredi";

    $consulta = $con->prepare($query);
    $consulta->execute();

    while ($data = $consulta->fetch(PDO::FETCH_ASSOC)) 
    {
        $return[] = array(
            "id"              => $data["id"],
            "datarecebimento" => $data["datarecebimento"],
            "numerobanco"     => $data["numerobanco"],
            "numeroagencia"   => $data["numeroagencia"],
            "nossonumero"     => $data["nossonumero"],            
            "json"            => $data["json"]            
        );
    }

    die(json_encode($return));

} else {
    http_response_code(400);
    echo "Erro: Banco não suportado para consulta: " .$banco;
}

?>