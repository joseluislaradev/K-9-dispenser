<?php
if (isset($_POST['wifiSSID'])) {
    require_once("../../Modelo/modelo.php");

    $params = array (
        "idUsuario" => $_POST["idUsuario"],
        "wifiSSID" => $_POST["wifiSSID"],
        "wifiPassword" => $_POST["wifiPassword"]	
    );

    //instancia y conexion con la BD
    $db = Database::getInstance();
    $conn = $db->getConnection();
    $sesion = new Modelo($conn);

    //Llamar a la funcion 'agregaUsuario' que se encuentra en el modelo
    list ($idDispensador, $valor, $error) = $sesion->registrarDispensador($params);
    if(empty($valor)){
        if(!empty($error)) {
            echo "error";
        }
    } else {
        echo $idDispensador;
    }
}
?>