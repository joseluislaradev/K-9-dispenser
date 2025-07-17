<?php

require_once("../Modelo/modelo.php");

    if(isset($_POST["horario"])){

        $idHorario = $_POST["idHorario"];

        //instancia y conexion con la BD
        $db = Database::getInstance();
        $conn = $db->getConnection();
        $sesion = new Modelo($conn);

        //Llamar a la funcion 'agregaUsuario' que se encuentra en el modelo
        list ($valor, $error) = $sesion->eliminarHorario($idHorario);
        if(empty($valor)){
            if(!empty($error)) {
                $_SESSION["error"] = $error;
            }
        } else {
            echo "<script> alert ('El horario fue eliminado exitosamente'); 
            window.location.href='../public_html/automatizacion.php';
            </script>";
        }


    }

?>