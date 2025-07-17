<?php
require_once("../Controlador/controlador.php");

@session_start();
$id = $_SESSION["id"];

if($_GET["token"]){

  $tokenRecibido = $_GET["token"];
  $existeToken = existeToken($id); //Si devuelve un valor quiere decir que ya hay uno guardado
  
  if($existeToken == $tokenRecibido){
    echo "<script> alert ('Los tokens no corresponden, inicia el proceso de nuevo'); 
    window.location.href='login.html';
    </script>";	  
  } 

?>


<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>k-9 dispenser</title>
    <!-- plugins:css -->
    <link rel="stylesheet" href="assets/vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="assets/vendors/css/vendor.bundle.base.css">
    <!-- endinject -->
    <!-- Plugin css for this page -->
    <!-- End Plugin css for this page -->
    <!-- inject:css -->
    <!-- endinject -->
    <!-- Layout styles -->
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- End layout styles -->
    <link rel="shortcut icon" href="assets/images/favicon.png" />
    <!-- Alertas -->
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <!-- end Alertas -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  </head>
  <body>
    <div class="content-wrapper" align="center">
      <div class="col-md-4 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <h4 class="card-title" align="center">Configura tu nueva contraseña</h4>
            <br>
            <p class="card-description">Recuerda poner una contraseña segura y que puedas recordar.</p>
            <br>
            <form class="forms-sample" id="miFormulario" action="../Controlador/cambiarContrasena.php" method="POST">
              <div class="form-group">
                <label for="contraseña">Ingresa una nueva contraseña: </label>
								<input type="text" name="contraseña" class="form-control" id="contraseña" autocomplete="off" minlength="8" maxlength="18" pattern="[A-Za-z][A-Za-z0-9]*[0-9][A-Za-z0-9]*" required title="Tu contraseña debe: 
- Comenzar con una letra
- Usar al menos una letra minúscula
- Usar al menos una letra mayúscula
- Tener minimo 8 caracteres
- Tener maximo 18 caracteres
- Tener un carácter numerico"/>
                  <i class="input-icon uil uil-lock-alt"></i>
                </div>
                <div class="form-group">
                <label for="contraseñaConfirmacion">Confirma tu contraseña: </label>
                  <input type="password" name="contraseñaConfirmacion" class="form-control" id="contraseñaConfirmacion" autocomplete="off" />
                  <i class="input-icon uil uil-lock-alt"></i>
                </div>

              <br>
              <div align="center">
                <input type="button" value="ENVIAR" onClick="comprobarClave()"  class="btn btn-primary">
                <a href="login.html" class="btn btn-dark">cancelar</a>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <footer class="footer">
      <div class="d-sm-flex justify-content-center justify-content-sm-between">
        <span class="text-muted d-block text-center text-sm-left d-sm-inline-block">© k9dispenser.com 2023</span>
      </div>
    </footer>

    <script>

      //Comprueba las contraseñas
      function comprobarClave() {
        clave1 = document.getElementById('contraseña').value;
        clave2 = document.getElementById('contraseñaConfirmacion').value;
    
        if (clave1 == clave2) {
          document.getElementById('miFormulario').submit();
        } else {
          alert("Las dos contraseñas ingresadas son diferentes, favor de verificarlas");
        }
      }
	  </script>

  </body>
</html>

<?php } ?>
