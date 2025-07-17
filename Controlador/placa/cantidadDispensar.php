<?php

if (isset($_POST['consultarCantidad'])) {
    require_once("../../Modelo/modelo.php");

    $idDispensador = $_POST['consultarCantidad'];

    //instancia y conexion con la BD
    $db = Database::getInstance();
    $conn = $db->getConnection();
    $sesion = new Modelo($conn);

    //Llamar a la funcion 'agregaUsuario' que se encuentra en el modelo
    list ($cantidad, $valor, $error) = $sesion->consultarCantidad($idDispensador);
    if(empty($valor)){
        if(!empty($error)) {
            echo "error";
        }
    } else {
        echo $cantidad;
    }

}

?>
