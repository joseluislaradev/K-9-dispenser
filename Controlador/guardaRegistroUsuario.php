
<?php
require_once("../Modelo/modelo.php");

@session_start();

//Bloqueo para que no se puedan registrar
echo "<script> alert ('LO SIENTO NO ESTAN PERTIDOS LSO REGISTROS EN ESTA DEMO.'); 
window.location.href='../public_html/login.html';
</script>";
die();

if($_POST) {
$params = array(
    "nombre" => $_POST["nombre"],
    "apellidoP" => $_POST["apellidoP"],
    "apellidoM" => $_POST["apellidoM"],
    "email" => $_POST["email"],
    "contraseña" => $_POST["contraseña"]
);

    //Sanitizacion de los POST
    $params["nombre"] = filter_var($params["nombre"] , FILTER_SANITIZE_STRING);
    $params["apellidoP"] = filter_var($params["apellidoP"] , FILTER_SANITIZE_STRING);
    $params["apellidoM"] = filter_var($params["apellidoM"] , FILTER_SANITIZE_STRING);
    $params["email"] = filter_var($params["email"] , FILTER_SANITIZE_EMAIL);


        $error_clave = "";

        if(strlen($params["contraseña"]) < 8){
            $error_clave = "La clave debe tener al menos 8 caracteres";
        }
        if(strlen($params["contraseña"]) > 18){
            $error_clave = "La clave no puede tener más de 18 caracteres";
        }
        if (!preg_match('`[a-z]`',$params["contraseña"])){
            $error_clave = "La clave debe tener al menos una letra minúscula";
        }
        if (!preg_match('`[A-Z]`',$params["contraseña"])){
            $error_clave = "La clave debe tener al menos una letra mayúscula";
        }
        if (!preg_match('`[0-9]`',$params["contraseña"])){
            $error_clave = "La clave debe tener al menos un caracter numérico";
        }
    
        if(empty($error_clave)){
    
            //instancia y conexion con la BD
            $db = Database::getInstance();
            $conn = $db->getConnection();
            $sesion = new Modelo($conn);
    
            $email = $params["email"];
    
            //Verificamos si existe el correo en la base de datos
            list ($valor, $error) = $sesion->verificarCorreo($email);
            if(empty($valor)){
                if(!empty($error)) {
                    $_SESSION["error"] = $error;
                }
            } else {
                echo "<script> alert ('El correo con el que intenta ingresar ya existe.'); 
                window.location.href='../public_html/login.html';
                </script>";
                die();
            }
    
            $valor = "";
            $error = "";
    
            //Encriptación de contraseña
            $params["contraseña"] = password_hash($params["contraseña"], PASSWORD_DEFAULT); // El costo es el numero de veces que se hashea la contraseña, indica que es mas complejo y tarda mas en generarse, el predeterminado es de 10 si no se pone
            
            //Llamar a la funcion 'agregaUsuario' que se encuentra en el modelo
            list ($valor, $error) = $sesion->agregaUsuario($params);
            if(empty($valor)){
                if(!empty($error)) {
                    $_SESSION["error"] = $error;
                }
            } else {    
                echo "<script> alert ('Su usuario fue registrado exitosamente'); 
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