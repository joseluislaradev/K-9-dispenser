<?php

if(!isset($_SESSION)){
    session_start();
}

require_once("../Modelo/modelo.php");

$params = array (
    "user"=>$_POST['logemail'],
    "pass" => $_POST['logpass'],
);

//Sanitizando correo
$params["user"] = filter_var($params["user"] , FILTER_SANITIZE_EMAIL);

//instancia de conexion bd
$db = Database::getInstance();
$conn = $db->getConnection();
$sesion = new Modelo($conn);

list ($valor1, $error1) = $sesion->usuarioBloqueadoVerificar($params);
//Si no se encontro error
if (empty($valor1)){
    //llamar a la funcion agregar usuario
    list ($valor, $error) = $sesion->validaUsuario($params);
    if (empty($valor)){
        echo "<script>alert('El usuario o la contraseña son incorrectos');
        window.location.href='../public_html/login.html';
        </script>";
    } else{
        echo "<script>alert('Bienvenido'); </script>";
        if ($_SESSION["tipoUsuario"] == 2){
            echo "<script>
            window.location.href='../public_html/indexAdministrador.php';
            </script>";
        } else if ($_SESSION["tipoUsuario"] == 1){
            echo "<script>
            window.location.href='../public_html/indexUsuario.php';
            </script>";
        } else {
            echo "<script>
            window.location.href='../public_html/login.html';
            </script>";
        }
    }

//Si se encontro un error
} else {
    echo "<script>alert('Tu cuenta se encuentra bloqueada por exceso de intentos de inicio de sesion. Contacta al administrador de la pagina: support@gmail.com');
    window.location.href='../public_html/login.html';
    </script>";
}





?>