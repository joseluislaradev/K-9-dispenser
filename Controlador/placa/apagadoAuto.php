<?php

if (isset($_POST['apagadoAuto'])) {
    require_once("../../Modelo/modelo.php");

    $idDispensador = $_POST['apagadoAuto'];	

    //instancia y conexion con la BD
    $db = Database::getInstance();
    $conn = $db->getConnection();
    $sesion = new Modelo($conn);

    //Llamar a la funcion 'agregaUsuario' que se encuentra en el modelo
    list ($valor, $error) = $sesion->apagadoAuto($idDispensador);
    if(empty($valor)){
        if(!empty($error)) {
            echo "error";
        }
    } else {
		echo "motor_is_off";
    }

}

?>
