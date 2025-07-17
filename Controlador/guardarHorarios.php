<?php

require_once("../Modelo/modelo.php");


function guardarHorario($idDispensador, $interpretacion, $tipo, $recurrencia, $fechaRecibida, $hora){

    $params = array(
        "idDispensador" => $idDispensador,
        "interpretacion" => $interpretacion,
        "tipo" => $tipo,
        "recurrencia" => $recurrencia,
        "fecha" => $fechaRecibida,
        "hora" => $hora 
    );

    //instancia y conexion con la BD
    $db = Database::getInstance();
    $conn = $db->getConnection();
    $sesion = new Modelo($conn);

    //Llamar a la funcion 'agregaUsuario' que se encuentra en el modelo
    list ($valor, $error) = $sesion->guardarHorarioBD($params);

    if(empty($valor)){
        if(!empty($error)) {
            $_SESSION["error"] = $error;
        }
    } else {
        echo "<script> alert ('AUTOMATIZACION AGREGADA EXITOSAMENTE'); 
        window.location.href='../public_html/automatizacion.php';
        </script>";
    }
}
