<?php

require_once("../Modelo/modelo.php");

if($_GET){
    //instancia y conexion con la BD
    $db = Database::getInstance();
    $conn = $db->getConnection();
    $sesion = new Modelo($conn);

    if(isset($_GET["grafica"])){
        @session_start();
        $grafica = $_GET['grafica'];
        $rangoTiempo = $_GET['rango'];
        $idDispensador = $_SESSION["idDispensador"];

        //Llamar a la funcion 'agregaUsuario' que se encuentra en el modelo
        list ($jsonData, $valor, $error) = $sesion->obtenerDatosGraficas($rangoTiempo, $grafica, $idDispensador);

        if(empty($valor)){
            if(!empty($error)) {
                $_SESSION["error"] = $error;
            }
        } else {
            header('Content-Type: application/json');
            echo $jsonData;
        }
    }
}



?>