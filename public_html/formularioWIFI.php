<?php
require_once("../Controlador/controlador.php");

@session_start();

if(isset($_SESSION["correo"])){
    $nombre=$_SESSION["nombre"];
    $id=$_SESSION["id"];

    if (isset($_POST['idDispensador'])) {
        $_SESSION["idDispensador"] = $_POST['idDispensador'];
    } 

    if ($_SESSION['idDispensador'] != 0) {

        $idDispensador = $_SESSION["idDispensador"];
        list ($nombreDispensador, $raza, $peso, $fotoDispensador, $descripcion, $comidaDisponibleActual, $comidaPlatoActual, $pesoDispensar, $estado, $wifiSSID, $wifiPassword) = dispensadores($idDispensador);


    } else {
        echo "<script>alert('Error de acceso. Porfavor accede a esta pagina desde el menu principal');
        window.location.href='indexUsuario.php';
        </script>";
    }


}else{
  session_destroy();
  echo "<script>alert('No has iniciado sesion');
  window.location.href='login.html';
  </script>";
}

if ($_SESSION["tipoUsuario"] != 1){
    echo "<script>alert('ACCESO DENEGADO');
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
            <h4 class="card-title" align="center">INGRESA LAS CREDENCIALES DE TU WIFI</h4>
            <br>
            <p class="card-description">Puedes consultar el nombre y contraseña de tu internet en tu modem wifi siempre y cuando nunca hayas cambiado la contraseña.</p>
            <br>
            <form class="forms-sample" action="../Controlador/actualizarWIFI.php" method="POST">
              <div class="form-group">
                <label for="wifiSSID">Ingresa el nombre de tu red (SSID):</label>
                <input type="text" class="form-control" id="wifiSSID" name="wifiSSID" placeholder="INFINITUM5C45_2.4" value="<?php echo $wifiSSID;?>" required>
              </div>
              <br>
              <div class="form-group">
                <label for="wifiPassword">Ingresa la contraseña de tu red:</label>
                <input type="text" class="form-control" id="wifiPassword" name="wifiPassword" placeholder="PJas5JrdAT" required>
              </div>
              <br>
              <div align="center">
                <button type="submit" class="btn btn-primary">CONECTAR</button>
                <a href="configuracionDispensador.php" class="btn btn-dark">cancelar</a>
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
  </body>
</html>
