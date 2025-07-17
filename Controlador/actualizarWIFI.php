<?php
require_once("../Controlador/controlador.php");


if($_POST){

    @session_start();

    $params = array(
        "wifiSSID" => $_POST['wifiSSID'],
        "wifiPassword" => $_POST['wifiPassword'], 
        "idDispensador" => $_SESSION["idDispensador"]
    ); 
 
    //instancia y conexion con la BD
    $db = Database::getInstance();
    $conn = $db->getConnection();
    $sesion = new Modelo($conn);

    //Llamar a la funcion 'agregaUsuario' que se encuentra en el modelo
    list ($valor, $error) = $sesion->actualizarWifi($params);
    if(empty($valor)){
        if(!empty($error)) {
            $_SESSION["error"] = $error;
        }
    } else {
        echo "<script> alert ('El wifiSSID y wifiPassword se actualizo exitosamente. Reinicia tu dispensador para efectuar el cambio.'); 
        window.location.href='../public_html/configuracionDispensador.php';
        </script>";
    }
}

?>