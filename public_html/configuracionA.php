<?php
require_once("../Controlador/controlador.php");

@session_start();

if(isset($_SESSION["correo"])){
    $nombre=$_SESSION["nombre"];
    $id=$_SESSION["id"];

    list ($nombre, $ape1, $ape2, $correo, $tipoUsuario) = perfil($id);
    $ayuda = 0;
    
    if(isset($_POST["editar"])){
      $ayuda = 1;
    }

    //Si se activa el boton de un dispensador
    if (isset($_POST['formatearDispensadores'])) {
      formatearDispensadores(2, $id); //Mando 2 para saber que son todos los dispensadores, el actual, mandare 1 cuando sean uno solo
    } 

    //Si se activa el boton de un dispensador
    if (isset($_POST['eliminarCuenta'])) {
      eliminarCuenta($id); 
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
  </head>
  <body>
    <div class="container-scroller">
      <!-- partial:partials/_sidebar.html -->
      <nav class="sidebar sidebar-offcanvas" id="sidebar">
        <div class="sidebar-brand-wrapper d-none d-lg-flex align-items-center justify-content-center fixed-top">
          <a class="sidebar-brand brand-logo" href="indexUsuario.php" style ="color:white; text-decoration:none;">K-9 DISPENSER</a>
          <a class="sidebar-brand brand-logo-mini" href="indexUsuario.php" style ="color:white; text-decoration:none;">K-9</a>
        </div>
        <ul class="nav">
          <li class="nav-item profile">
            <div class="profile-desc">
              <div class="profile-pic">
                <div class="count-indicator">
                  <img class="img-xs rounded-circle " src="<?php echo ($_SESSION["ruta"] != "")?$_SESSION['ruta']:"assets/images/faces-clipart/iconoPersona.jpeg"?>" alt="">
                  <span class="count bg-success"></span>
                </div>
                <div class="profile-name">
                  <h5 class="mb-0 font-weight-normal"><?php echo $nombre; ?> </h5>
                </div>
              </div>
            </div>
          </li>
          <li class="nav-item nav-category">
            <span class="nav-link">Barra de navegación</span>
          </li>
          <li class="nav-item menu-items">
            <a class="nav-link" href="configuracion.php">
              <span class="menu-icon">
                <i class="mdi mdi-account"></i>
              </span>
              <span class="menu-title">Cuenta</span>
            </a>
          </li>
          <li class="nav-item menu-items">
            <a class="nav-link" href="configuracionA.php">
              <span class="menu-icon">
                <i class="mdi mdi-settings"></i>
              </span>
              <span class="menu-title">Avanzadas</span>
            </a>
          </li>
          <br><br><br><br><br><br><br><br><br><br>
          <li class="nav-item menu-items">
            <a class="nav-link" href="indexUsuario.php">
              <span class="menu-icon">
                <i class="mdi mdi-arrow-left"></i>
              </span>
              <span class="menu-title">REGRESAR</span>
            </a>
          </li>

        </ul>
      </nav>
      <!-- partial -->
      <div class="container-fluid page-body-wrapper">
        <!-- partial:partials/_navbar.html -->
        <nav class="navbar p-0 fixed-top d-flex flex-row">
          <div class="navbar-brand-wrapper d-flex d-lg-none align-items-center justify-content-center">
            <a class="navbar-brand brand-logo-mini" href="indexUsuario.php"><img src="assets/images/logo-mini.svg" alt="logo" /></a>
          </div>
          <div class="navbar-menu-wrapper flex-grow d-flex align-items-stretch">
            <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize">
              <span class="mdi mdi-menu"></span>
            </button>
            <ul class="navbar-nav w-100">
              <li class="nav-item w-100">
                  <h1 style="text-align: center;">Configuración Avanzada</h1>
              </li>
            </ul>
            <ul class="navbar-nav navbar-nav-right">

              <li class="nav-item dropdown border-left">
                <a class="nav-link count-indicator dropdown-toggle" id="notificationDropdown" href="#" data-toggle="dropdown">
                  <i class="mdi mdi-bell"></i>
                  <span class="count bg-danger"></span>
                </a>
                <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list" aria-labelledby="notificationDropdown">
                  <h6 class="p-3 mb-0">Notifications</h6>
                  <div class="dropdown-divider"></div>
                  <a class="dropdown-item preview-item">
                    <div class="preview-thumbnail">
                      <div class="preview-icon bg-dark rounded-circle">
                        <i class="mdi mdi-calendar text-success"></i>
                      </div>
                    </div>
                    <div class="preview-item-content">
                      <p class="preview-subject mb-1">Event today</p>
                      <p class="text-muted ellipsis mb-0"> Just a reminder that you have an event today </p>
                    </div>
                  </a>
                  <div class="dropdown-divider"></div>
                  <a class="dropdown-item preview-item">
                    <div class="preview-thumbnail">
                      <div class="preview-icon bg-dark rounded-circle">
                        <i class="mdi mdi-settings text-danger"></i>
                      </div>
                    </div>
                    <div class="preview-item-content">
                      <p class="preview-subject mb-1">Settings</p>
                      <p class="text-muted ellipsis mb-0"> Update dashboard </p>
                    </div>
                  </a>
                  <div class="dropdown-divider"></div>
                  <a class="dropdown-item preview-item">
                    <div class="preview-thumbnail">
                      <div class="preview-icon bg-dark rounded-circle">
                        <i class="mdi mdi-link-variant text-warning"></i>
                      </div>
                    </div>
                    <div class="preview-item-content">
                      <p class="preview-subject mb-1">Launch Admin</p>
                      <p class="text-muted ellipsis mb-0"> New admin wow! </p>
                    </div>
                  </a>
                  <div class="dropdown-divider"></div>
                  <p class="p-3 mb-0 text-center">See all notifications</p>
                </div>
              </li>
              <li class="nav-item dropdown">
                <a class="nav-link" id="profileDropdown" href="#" data-toggle="dropdown">
                  <div class="navbar-profile">
                    <img class="img-xs rounded-circle" src="<?php echo ($_SESSION["ruta"] != "")?$_SESSION['ruta']:"assets/images/faces-clipart/iconoPersona.jpeg"?>" alt="">
                    <p class="mb-0 d-none d-sm-block navbar-profile-name"><?php echo $nombre; ?></p>
                    <i class="mdi mdi-menu-down d-none d-sm-block"></i>
                  </div>
                </a>
                <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list" aria-labelledby="profileDropdown">
                  <h6 class="p-3 mb-0">Perfil</h6>
                  <div class="dropdown-divider"></div>
                  <div class="dropdown-divider"></div>
                  <a onclick="EventoAlert()" class="dropdown-item preview-item">
                    <div class="preview-thumbnail">
                      <div class="preview-icon bg-dark rounded-circle">
                        <i class="mdi mdi-logout text-danger"></i>
                      </div>
                    </div>
                    <div class="preview-item-content">
                      <p class="preview-subject mb-1">Cerrar Sesión</p>
                    </div>
                  </a>
              </li>
            </ul>
            <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-toggle="offcanvas">
              <span class="mdi mdi-format-line-spacing"></span>
            </button>
          </div>
        </nav>
        <!-- partial -->
        <div class="main-panel">
          <div class="content-wrapper">
            <div class="row">
              <div class="col-md-6 grid-margin stretch-card" align="center">
                <div class="card">
                  <div class="card-body" >
                  <h4 class="card-title">Cambiar contraseña</h4>
                    <p class="card-description">Asegúrate de elegir una contraseña segura y única para proteger tu cuenta y mantener tus datos personales protegidos. </p>
                    <div class="template-demo"> 
                      <br>
                      <a href="pantallaEspera.php"><button type="button" class="btn btn-danger btn-lg">Cambiar contraseña</button></a>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-md-6 grid-margin stretch-card" align="center">
                <div class="card">
                  <div class="card-body" >
                  <h4 class="card-title">Formatear dispensadores</h4>
                    <p class="card-description">Todos los datos recolectados por TODOS los dispensadores se ELIMINARAN PERMANENTEMENE. Los dispensadores podran ser ligados a otra cuenta y reiniciaran sus configuraciones.</p>
                    <div class="template-demo">
                      <br>
                      <form action="configuracionA.php" method="POST">
                        <button type="submit" name="formatearDispensadores" class="btn btn-danger btn-lg">Formatear dispensador</button>
                      </form>                    
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-md-6 grid-margin stretch-card" align="center">
                <div class="card">
                  <div class="card-body" >
                  <h4 class="card-title">Eliminar cuenta</h4>
                    <p class="card-description">Todos los datos guardados y dispensadores ligados a la cuenta se ELIMINARAN PERMANENTEMENTE. Los dispensadores podran ser ligados a otra cuenta y reiniciaran sus configuraciones.</p>
                    <div class="template-demo">
                      <br>
                      <form action="configuracionA.php" method="POST">
                        <button type="submit" name="eliminarCuenta" class="btn btn-danger btn-lg">ELIMINAR CUENTA</button>
                      </form>                       
                    </div>
                  </div>
                </div>
              </div>


            </div>
          </div>
          <!-- content-wrapper ends -->
          <!-- partial:partials/_footer.html -->
          <footer class="footer">
            <div class="d-sm-flex justify-content-center justify-content-sm-between">
              <span class="text-muted d-block text-center text-sm-left d-sm-inline-block">Copyright © k9dispenser.com 2023</span>
            </div>
          </footer>
          <!-- partial -->
        </div>
        <!-- main-panel ends -->
      </div>
      <!-- page-body-wrapper ends -->
    </div>
    <!-- container-scroller -->
    <!-- plugins:js -->
    <script src="assets/vendors/js/vendor.bundle.base.js"></script>
    <!-- endinject -->
    <!-- Plugin js for this page -->
    <!-- End plugin js for this page -->
    <!-- inject:js -->
    <script src="assets/js/off-canvas.js"></script>
    <script src="assets/js/hoverable-collapse.js"></script>
    <script src="assets/js/misc.js"></script>
    <script src="assets/js/settings.js"></script>
    <script src="assets/js/todolist.js"></script>
    <!-- endinject -->
    <!-- Custom js for this page -->
    <!-- End custom js for this page -->

    <script>
        function EventoAlert(){
            swal({
            title: "¿Seguro que quieres cerrar sesion?",
            text: "Tendras que colocar tus credenciales de acceso para volver a entrar",
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