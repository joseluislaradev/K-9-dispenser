<?php
require_once("../Controlador/controlador.php");

@session_start();

if(isset($_SESSION["correo"])){
    $nombre=$_SESSION["nombre"];

    //Comprobamos el tiempo que lleva para cerrar sesion automaticamente
    $fechaGuardada = $_SESSION["ultimoAcceso"];
    $ahora = date("Y-n-j H:i:s");
    $tiempo_transcurrido = (strtotime($ahora)-strtotime($fechaGuardada));
    //comparamos el tiempo transcurrido
    if($tiempo_transcurrido >= 120) {
        //si pasaron 10 minutos o más
        session_destroy(); // destruyo la sesión
        echo "<script>alert('Sesión cerrada por inactividad. Por favor, vuelve a iniciar sesión');
        window.location.href='login.html';
        </script>";   
    //sino, actualizo la fecha de la sesión
    }else {
        $_SESSION["ultimoAcceso"] = $ahora;
    }


}else{
session_destroy();
echo "<script>alert('No has iniciado sesion');
window.location.href='login.html';
</script>";
}

if ($_SESSION["tipoUsuario"] != 2){
    echo "<script>alert('ACCESO DENEGADO');
    window.location.href='login.html';
    </script>";
} 

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Index</title>

    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/css/bootstrap.min.css'>
	<link rel='stylesheet' href='https://unicons.iconscout.com/release/v2.1.9/css/unicons.css'><link rel="stylesheet" href="./style.css">
	<meta name="author" content="JoseLuisArredondoLara, EduardoVargasVaca, JonathanLopezMendez">
    
    <!-- Alertas -->
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
</head>
<body>
    <h1 align="center"><?php echo "Bienvenido Administrador ". $nombre; ?> </h1>
    
    <ul class="nav">
		<li><a onclick = "EventoAlert()">CERRAR SESIÓN</a></li>
	</ul>

    
    <!-- Nos muestra la ventana emergente para confirmar si queremos salir del formulario -->
    <script>
        function EventoAlert(){
            swal({
            title: "¿Seguro que quieres cerrar sesion?",
            text: "Asegurate de enviar cualquier dato para evitar perdidas.",
            icon: "warning",
            buttons: true, 
            dangerMode: true,
            })
            .then((willDelete) => {
                if (willDelete) {
                    window.location.href = "../Controlador/cerrarSesion.php";
                } 
            });
        }
    </script>


</body>
</html>