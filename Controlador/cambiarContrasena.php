
<?php
require_once("../Modelo/modelo.php");

@session_start();


if($_POST) {
    $contrasena = $_POST["contraseña"];

        $error_clave = "";

        if(strlen($contrasena) < 8){
            $error_clave = "La clave debe tener al menos 8 caracteres";
        }
        if(strlen($contrasena) > 18){
            $error_clave = "La clave no puede tener más de 18 caracteres";
        }
        if (!preg_match('`[a-z]`',$contrasena)){
            $error_clave = "La clave debe tener al menos una letra minúscula";
        }
        if (!preg_match('`[A-Z]`',$contrasena)){
            $error_clave = "La clave debe tener al menos una letra mayúscula";
        }
        if (!preg_match('`[0-9]`',$contrasena)){
            $error_clave = "La clave debe tener al menos un caracter numérico";
        }
    
        if(empty($error_clave)){
    
            //instancia y conexion con la BD
            $db = Database::getInstance();
            $conn = $db->getConnection();
            $sesion = new Modelo($conn);

            //Encriptación de contraseña
            $contrasena = password_hash($contrasena, PASSWORD_DEFAULT); // El costo es el numero de veces que se hashea la contraseña, indica que es mas complejo y tarda mas en generarse, el predeterminado es de 10 si no se pone
            
            @session_start();
            $id = $_SESSION["id"];

            //Llamar a la funcion 'agregaUsuario' que se encuentra en el modelo
            list ($valor, $error) = $sesion->cambiarContrasena($contrasena, $id);
            if(empty($valor)){
                if(!empty($error)) {
                    $_SESSION["error"] = $error;
                }
            } else {
                echo "<script> alert ('Tu contraseña fue cambiada exitosamente'); 
                window.location.href='../public_html/login.html';
                </script>";
            }
        } else {
            echo "<script> alert ('Tu contraseña debe: Comenzar con una letra, usar al menos una letra minúscula, usar al menos una letra mayúscula, tener minimo 8 caracteres, tener maximo 16 caracteres, tener un carácter numerico'); 
            window.history.back();
            </script>";
        }

}

?>