<?php

require_once("../Modelo/modelo.php");

if(isset($_POST)){

    //instancia y conexion con la BD
    $db = Database::getInstance();
    $conn = $db->getConnection();
    $sesion = new Modelo($conn);


    //Si da click en el boton eliminar desde la tabla, es decir eliminar solo un registro, el post trae el id
    if(isset($_POST["eliminarUno"])){

        $idInfoDispensacion = $_POST["eliminarUno"]; //Pasandole el id del registro a eliminar

        //Llamar a la funcion 'agregaUsuario' que se encuentra en el modelo
        list ($valor, $error) = $sesion->eliminarInfoDispensacion($idInfoDispensacion);
        if(empty($valor)){
            if(!empty($error)) {
                $_SESSION["error"] = $error;
            }
        } else {
            echo "<script> alert ('El registro fue eliminado exitosamente'); 
            window.location.href='../public_html/historial.php';
            </script>";
        }
    }

    //Si da click en el boton eliminar todos los registros, el post trae el id pero del dispensador
    if(isset($_POST["eliminarTodos"])){

        $idDispensador = $_POST["eliminarTodos"]; //Pasandole id del dispensador del cual se eleimnara todo

        //Llamar a la funcion 'agregaUsuario' que se encuentra en el modelo
        list ($valor, $error) = $sesion->eliminarTodoInfoDispensacion($idDispensador);
        if(empty($valor)){
            if(!empty($error)) {
                $_SESSION["error"] = $error;
            }
        } else {
            echo "<script> alert ('Toda la información fue eliminada exitosamente'); 
            window.location.href='../public_html/historial.php';
            </script>";
        }
    }
}

?>