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

        //Si se activa el boton de un dispensador
        if (isset($_POST['activarMotor'])) {
            $_SESSION['pagina'] = "indexDispensador";
            actualizarEstadoDispensador($idDispensador);
        } 

        //Si se activa el boton de cambiar peso
        if($_POST){
          if(isset($_POST["peso"])){
            $peso = $_POST["peso"];
            cambiarPeso($peso, $idDispensador);
          }
        }

        list ($nombreDispensador, $raza, $peso, $fotoDispensador, $descripcion, $comidaDisponibleActual, $comidaPlatoActual, $pesoDispensar, $estado, $wifiSSID, $wifiPassword) = dispensadores($idDispensador);
        list($fechaAutomatizacion, $horaAutomatizacion) = c_proximoHorario($idDispensador);

        $interpretacion2 = "";
        $interpretacion3 = "";
        if($fechaAutomatizacion){

          // Convertir a formato de 12 horas
          $hora12 = date("h:i A", strtotime($horaAutomatizacion));

          // Convertir la fecha a timestamp
          $timestamp = strtotime($fechaAutomatizacion);
          setlocale(LC_TIME, 'es_ES.UTF-8'); // Establecer el local a español
          // Obtener el nombre del día de la semana
          $nombreDia = date("l", $timestamp);

          // Obtener el día del mes
          $dia = date("d", $timestamp);

          // Obtener el nombre del mes
          $nombreMes = date("F", $timestamp);

          // Obtener el año
          $anio = date("Y", $timestamp);

          // Convertir el nombre del día de la semana a minúsculas y corregir tildes
          $nombreDia = ucfirst(str_replace(array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), array('lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado', 'domingo'), $nombreDia));

          // Convertir el nombre del mes a minúsculas y corregir tildes
          $nombreMes = ucfirst(str_replace(array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'), array('enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'), $nombreMes));

          //Comienzo la interpretacion2
          // Obtener la fecha actual
          $fechaHoy = new DateTime(); // Esto obtiene la fecha y hora actual

          // Convertir la fecha guardada a objeto DateTime
          $fechaGuardadaObj = new DateTime($fechaAutomatizacion);

          // Comparar las fechas
          if ($fechaGuardadaObj == $fechaHoy) {
              $interpretacion2 .= "Hoy a las ". $horaAutomatizacion. " horas";
          } elseif ($fechaGuardadaObj == $fechaHoy->modify('+1 day')) {
            $interpretacion2 .= "Mañana a las ". $horaAutomatizacion. " horas";
          } else {
            $interpretacion2 .= "El dia $nombreDia $dia de $nombreMes del año $anio a las $hora12";
          }
        } else {
          $interpretacion2 .= "No hay ninguna automatización programada";
        }

        //Comenzamos la interpretacion 3
        list($fechaUltimaDispensacion, $horaUltimaDispensacion) = c_ultimaDispensacion($idDispensador);
        if($fechaUltimaDispensacion){

        date_default_timezone_set('America/Mexico_City');

        // Obtener la fecha y hora actual
        $fechaHoraActual = new DateTime(); // Esto obtiene la fecha y hora actual

        // Convertir la fecha guardada y hora a objetos DateTime
        $fechaGuardadaObj = new DateTime($fechaUltimaDispensacion . ' ' . $horaUltimaDispensacion);

        // Calcular la diferencia entre las dos fechas y horas
        $diferencia = $fechaHoraActual->diff($fechaGuardadaObj);

        // Imprimir la diferencia en días, horas, minutos y segundos
        $formatoDiferencia = '';
        if ($diferencia->d > 0) {
            $formatoDiferencia .= $diferencia->d . ' días, ';
        }
        if ($diferencia->h > 0) {
            $formatoDiferencia .= $diferencia->h . ' horas, ';
        }
        if ($diferencia->i > 0) {
            $formatoDiferencia .= $diferencia->i . ' minutos, ';
        }
        if ($diferencia->s > 0) {
            $formatoDiferencia .= $diferencia->s . ' segundos';
        }

        // Imprimir la diferencia en días, horas, minutos y segundos
        $interpretacion3 .= "Hace ".trim($formatoDiferencia, ', ');
        
      } else {
        $interpretacion3 .= "Historial vacio";
      }

      $_SESSION["configurar"] = 0;
      if($_GET){
        if($_GET["configurar"] == 1){
          $_SESSION["configurar"] = 1;
        }
      }


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
                  <img class="img-sm rounded-circle" src="<?php echo ($fotoDispensador != "")?$fotoDispensador:"assets/images/perritoElegante.png"?>" alt="">
                  <span class="count bg-success"></span>
                </div>
                <div class="profile-name">
                  <h5 class="mb-0 font-weight-normal"><?php echo $nombreDispensador; ?> </h5>
                </div>
              </div>
            </div>
          </li>
          <li class="nav-item nav-category">
            <span class="nav-link">Barra de navegación</span>
          </li>
          <li class="nav-item menu-items">
            <a class="nav-link" href="dispensador.php">
              <span class="menu-icon">
                <i class="mdi mdi-speedometer"></i>
              </span>
              <span class="menu-title">Resumen</span>
            </a>
          </li>
          <li class="nav-item menu-items">
            <a class="nav-link" href="automatizacion.php">
              <span class="menu-icon">
                <i class="mdi mdi-auto-fix"></i>
              </span>
              <span class="menu-title">Automatización</span>
            </a>
          </li>
          <li class="nav-item menu-items">
            <a class="nav-link" href="historial.php">
              <span class="menu-icon">
                <i class="mdi mdi-history"></i>
              </span>
              <span class="menu-title">Historial</span>
            </a>
          </li>
          <li class="nav-item menu-items">
            <a class="nav-link" href="analisis.php">
              <span class="menu-icon">
                <i class="mdi mdi-chart-line"></i>
              </span>
              
              <span class="menu-title">Análisis</span>
            </a>
          </li>
          <li class="nav-item menu-items">
            <a class="nav-link" href="perfilDispensador.php">
              <span class="menu-icon">
                <i class="mdi mdi-account"></i>
              </span>
              <span class="menu-title">Perfil del dispensador</span>
            </a>
          </li>
          <li class="nav-item menu-items">
            <a class="nav-link" href="configuracionDispensador.php">
              <span class="menu-icon">
                <i class="mdi mdi-settings"></i>
              </span>
              
              <span class="menu-title">Configuración</span>
            </a>
          </li>
          <br><br><br>
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
                <h1 style="text-align: center;"> <?php echo ($estado == 1)?"ENCENDIDO":"APAGADO"; ?></h1>
              </li>
            </ul>
            <ul class="navbar-nav navbar-nav-right">
              <li class="nav-item dropdown d-none d-lg-block">

                <form action="dispensador.php" id="agregarDispensador" method="POST" enctype="multipart/form-data">
                    <input type="submit" class="nav-link btn btn-success create-new-button"  aria-expanded="false" id="submit_button" name="activarMotor" value="<?php echo ($estado == 1)?"DISPENSANDO COMIDA":"DISPENSAR COMIDA"; ?>" <?php echo ($estado == 1)?"disabled":""; ?>/>
                </form>

                <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list" aria-labelledby="createbuttonDropdown">
                  <h6 class="p-3 mb-0">Projects</h6>
                  <div class="dropdown-divider"></div>
                  <a class="dropdown-item preview-item">
                    <div class="preview-thumbnail">
                      <div class="preview-icon bg-dark rounded-circle">
                        <i class="mdi mdi-file-outline text-primary"></i>
                      </div>
                    </div>
                    <div class="preview-item-content">
                      <p class="preview-subject ellipsis mb-1">Software Development</p>
                    </div>
                  </a>
                  <div class="dropdown-divider"></div>
                  <a class="dropdown-item preview-item">
                    <div class="preview-thumbnail">
                      <div class="preview-icon bg-dark rounded-circle">
                        <i class="mdi mdi-web text-info"></i>
                      </div>
                    </div>
                    <div class="preview-item-content">
                      <p class="preview-subject ellipsis mb-1">UI Development</p>
                    </div>
                  </a>
                  <div class="dropdown-divider"></div>
                  <a class="dropdown-item preview-item">
                    <div class="preview-thumbnail">
                      <div class="preview-icon bg-dark rounded-circle">
                        <i class="mdi mdi-layers text-danger"></i>
                      </div>
                    </div>
                    <div class="preview-item-content">
                      <p class="preview-subject ellipsis mb-1">Software Testing</p>
                    </div>
                  </a>
                  <div class="dropdown-divider"></div>
                  <p class="p-3 mb-0 text-center">See all projects</p>
                </div>
              </li>
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
                  <a href="configuracion.php" class="dropdown-item preview-item">
                    <div class="preview-thumbnail">
                      <div class="preview-icon bg-dark rounded-circle">
                        <i class="mdi mdi-settings text-success"></i>
                      </div>
                    </div>
                    <div class="preview-item-content">
                      <p class="preview-subject mb-1">Configuración</p>
                    </div>
                  </a>
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
              <div class="col-sm-4 grid-margin">
                <div class="card">
                  <div class="card-body">
                    <h5>Comida Almacenada</h5>
                    <div class="row">
                      <div class="col-8 col-sm-12 col-xl-8 my-auto">
                        <div class="d-flex d-sm-block d-md-flex align-items-center">
                          <h2 class="mb-0"><?php echo $comidaDisponibleActual ?></h2>
                          <p class="text-success ml-2 mb-0 font-weight-medium">gramos</p>
                        </div>
                        <h6 class="text-muted font-weight-normal">Actualmente</h6>
                      </div>
                      <div class="col-4 col-sm-12 col-xl-4 text-center text-xl-right">
                        <i class="icon-lg mdi mdi-codepen text-primary ml-auto"></i>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-sm-4 grid-margin">
                <div class="card">
                  <div class="card-body">
                    <h5>Comida en el plato</h5>
                    <div class="row">
                      <div class="col-8 col-sm-12 col-xl-8 my-auto">
                        <div class="d-flex d-sm-block d-md-flex align-items-center">
                          <h2 class="mb-0"><?php echo $comidaPlatoActual  ?></h2>
                          <p class="text-success ml-2 mb-0 font-weight-medium">gramos</p>
                        </div>
                        <h6 class="text-muted font-weight-normal">Actualmente</h6>
                      </div>
                      <div class="col-4 col-sm-12 col-xl-4 text-center text-xl-right">
                        <i class="icon-lg mdi mdi mdi-bone text-danger ml-auto"></i>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-sm-4 grid-margin">
                <div class="card">
                  <div class="card-body">
                    <h5>Peso que dispensa</h5>
                    <div class="row">
                      <div class="col-8 col-sm-12 col-xl-8 my-auto">
                        <div class="d-flex d-sm-block d-md-flex align-items-center">
                          <?php if($_SESSION["configurar"] == 0) { ?>
                          <h2 class="mb-0"><?php echo $pesoDispensar; ?></h2>
                          <p class="text-danger ml-2 mb-0 font-weight-medium">gramos</p>
                        </div>
                        <h6 class="text-muted font-weight-normal"><a href="dispensador.php?configurar=1">Configurar</a></h6>
                        <?php } else { ?>
                          <form action="dispensador.php" method="POST">
                            <input type="number" name="peso" required/> 
                            <p class="text-danger ml-2 mb-0 font-weight-medium">gramos</p>
                            <button type="submit" class="btn btn-primary mr-2" name="enviar">Enviar</button>
                            <a href="dispensador.php" class="btn btn-dark" >Cancelar</a>
                          </form>
                          </div>
                        <?php } ?>
                      </div>
                      <div class="col-4 col-sm-12 col-xl-4 text-center text-xl-right">
                        <i class="icon-lg mdi mdi mdi-weight text-success ml-auto"></i>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-sm-4 grid-margin">
                <div class="card">
                  <div class="card-body" >
                    <h4 class="card-title" align = "center">Datos Generales</h4>
                  </div>
                  <img src="<?php echo ($fotoDispensador != "")?$fotoDispensador:"assets/images/perritoElegante.png"?>" class="rounded-circle"  alt=""> 
                  <br>
                  <p class="text-muted"  align = "center"><?php echo $descripcion; ?></p>
                  <h6 align = "center"><b>Raza: </b><?php  echo ($raza != "")?$raza:" No especificado"; ?> </h6>
                  <h6 align = "center"><b>Peso: </b> <?php echo ($peso != 0)?$peso." kilogramos":" No especificado"; ?></h6>
                </div>
              </div>
              <div class="col-sm-8 grid-margin">
                <div class="card">
                  <div class="card-body" >
                    <h4 class="card-title" align = "center">Próxima Automatización</h4>
                  </div>
                  <h4 class="text-muted"  align = "center"><?php echo $interpretacion2; ?></h4>
                </div>
                <br>
                <div class="card">
                  <div class="card-body" >
                    <h4 class="card-title" align = "center">Última Dispensación</h4>
                  </div>
                  <h4 class="text-muted"  align = "center"><?php echo $interpretacion3; ?></h4>
                </div>
                <br>
                <div class="card">
                  <div class="card-body">
                    <h5 align = "center">WIFI CONFIGURADO</h5>
                    <div class="row">
                      <div class="col-6 col-sm-12 col-xl-8 my-auto">
                        <h3 class="mb-0" align="center"><?php echo $wifiSSID; ?></h3>
                      </div>
                      <div class="col-4 col-sm-12 col-xl-4 text-left">
                        <i class="icon-lg mdi mdi-wifi text-success ml-auto"></i>
                      </div>
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