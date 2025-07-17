<?php
require_once("../Controlador/controlador.php");
require_once("../Controlador/guardarHorarios.php");


@session_start();

if(isset($_SESSION["correo"])){
    $nombre=$_SESSION["nombre"];
    $id=$_SESSION["id"];

    if (isset($_POST['idDispensador'])) {
        $_SESSION["idDispensador"] = $_POST['idDispensador'];
    } 
 
    if ($_SESSION['idDispensador'] != 0) { //Si el id es diferente de 0 quiere decir que entramos desde el menu principal recibiendo el POST
        $idDispensador = $_SESSION["idDispensador"];
        $tipo = "";
        $recurrencia = "";
        $fechaRecibida = "";
        $fecha = "";
        $hora = "";

        //Recibiendo los datos que ponen dentro de el formulario para configurar un horario
        if (isset($_POST['tipo'])) {
          $tipo = $_POST['tipo'];
        } 
        if (isset($_POST['recurrencia'])) {
          $recurrencia = $_POST['recurrencia'];
        } 
        if (isset($_POST['fecha'])) {
          $fechaRecibida = $_POST['fecha']; //fecha a enviar a mysql
  
          // Convertir la fecha a timestamp
          $timestamp = strtotime($fechaRecibida);
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

          // Construir la fecha en texto legible
          $fecha = "día $nombreDia $dia de $nombreMes del $anio";
        } 
        if (isset($_POST['hora'])) {
          $hora = $_POST['hora'];
        } 


        
        $interpretacion = "";
        //Comienza la interpretacion de las respuestas que se recibieron
        if ($tipo != "") { 
            if ($tipo == "recurrente") {
              $interpretacion .= "Todos los "; 
            } else if ($tipo == "unaVez"){
              $interpretacion .= "Solo una vez el "; 

              if($fechaRecibida != ""){
                $interpretacion .=  $fecha;
                if($hora != ""){
                  $interpretacion .=  " a las " . $hora . " horas.";
                }
              }
            }
        } else {
          $interpretacion .=  "Comienza a formar tus horarios de automatizacion para comenzar la interpretación";
        }

        //Comienza interpretacion de segundo select
        if($recurrencia != ""){
          if($recurrencia == "diario" && $tipo == "recurrente"){
            $interpretacion .=  "dias apartir de el ";
            if($fechaRecibida != ""){
              $interpretacion .=  $fecha;
              if($hora != ""){
                $interpretacion .=  " a las " . $hora . " horas.";
              }
            }
          } else if ($recurrencia == "semanas" && $tipo == "recurrente") {
            $interpretacion .=  "dias ";
            if($fechaRecibida != ""){
              $interpretacion .=  $nombreDia . " apartir del " . $fecha;
              if($hora != ""){
                $interpretacion .=  " a las " . $hora . " horas.";
              }
            }
          } else if ($recurrencia == "diaMes" && $tipo == "recurrente") {
            $interpretacion .=  "dias numero ";
            if($fechaRecibida != ""){
              $interpretacion .=  $dia . " de cada mes apartir del " . $fecha;
              if($hora != ""){
                $interpretacion .=  " a las " . $hora . " horas.";
              }
            } 
          }
        }

        
        //Si el boton estuvo disponible quiere decir que ya se lleno todo lo que se necesita
        if (isset($_POST["enviar"])) { 
          guardarHorario($idDispensador, $interpretacion, $tipo, $recurrencia, $fechaRecibida, $hora);
        } 

        //Si se activa el boton de un dispensador
        if (isset($_POST['activarMotor'])) {
            $_SESSION['pagina'] = "automatizacion";
            actualizarEstadoDispensador($idDispensador);
        } 

        // Consultando datos para llenar la tabla que contiene los horarios ya configurados en la cuenta
        list ($nombreDispensador, $raza, $peso, $fotoDispensador, $descripcion, $comidaDisponibleActual, $comidaPlatoActual, $pesoDispensar, $estado, $wifiSSID, $wifiPassword) = dispensadores($idDispensador);

        $cHorarios = C_horarios($idDispensador);

    } else {
        echo "<script>alert('Error de acceso. Porfavor accede a esta pagina desde el menu principal');
        window.location.href='indexUsuario.php';
        </script>";
    }


//Si no tiene un correo en variable de sesion quiere decir que no inicio sesion
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
              <div class="col-md-4 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                    <h4 class="card-title" align="center">Configurar Automatización</h4>
                      <br>
                      <!--Comienza el formulario para crear un nuevo horario-->
                      <form action="automatizacion.php" method="POST" class="forms-sample" id="form3">
                          <div class="form-group">
                            <label for="tipo" class="form-label">Tipo de programacion: </label>
                            <select style = 'color:white' id="tipo" class="form-control" required style="font-size:12px" name = "tipo" onchange = "recargarPagina();">
                              <option style = 'color:white' value="">Elija uno...</option>
                              <option style = 'color:white' value="recurrente" <?php echo ($tipo == "recurrente")?"selected":"";  ?> >Recurrente</option>
                              <option style = 'color:white' value="unaVez" <?php echo ($tipo == "unaVez")?"selected":"";  ?>>Una vez</option>
                            </select>
                          </div>

                          <?php if($tipo == "recurrente"){ ?>
                          <div class="form-group">
                            <label for="recurrencia" class="form-label">Recurrencia: </label>
                            <select style = 'color:white'  id="recurrencia" class="form-control" required style="font-size:12px" name = "recurrencia" onchange = "recargarPagina();">
                              <option style = 'color:white'  value="">Elija uno...</option>
                              <option style = 'color:white'  value="diario" <?php echo ($recurrencia == "diario")?"selected":"";  ?> >Diaria</option>
                              <option style = 'color:white'  value="semanas" <?php echo ($recurrencia == "semanas")?"selected":"";  ?>>Por dia de la semana</option>
                              <option style = 'color:white'  value="diaMes" <?php echo ($recurrencia == "diaMes")?"selected":"";  ?>>Por dia del mes</option>
                            </select>
                          </div>
                          <?php } ?>

                          <?php if($tipo == "unaVez" || ($recurrencia != "" && $tipo == "recurrente")){ ?>
                          <div class="form-group">
                            <label for="fecha" class="form-label">Fecha de inicio</label>
                            <input style = 'color: white' type="date" class="form-control"  id="fecha" name="fecha" value="<?php echo ($fechaRecibida != "")?$fechaRecibida:"";  ?>" min="<?php echo date('Y-m-d'); ?>" required>
                          </div>

                          <div class="form-group">
                            <label for="hora" class="form-label">Hora de dispensación</label>
                            <input style = 'color: white' type="time" class="form-control" id="hora" name="hora" value="<?php echo ($hora != "")?$hora:"";  ?>" required>
                          </div>

                          <?php } if(($fecha != "" && $hora != "") && ($recurrencia != "" || $tipo == "unaVez")){ ?>
                            <br>
                            <div align="center">
                              <input type="submit" class="btn btn-primary mr-2" name="enviar" value="Programar Automatización"/>
                            </div> 
                          <?php } ?>


                  
                          <script >
                            function recargarPagina(){
                              document.getElementById("form3").submit(); //Enviando formulario para que la pagina se recargue con lo que el usuario selecciono
                            }

                            // Obtén los elementos de entrada date y time por sus IDs
                            const fechaInput = document.getElementById('fecha');
                            const horaInput = document.getElementById('hora');

                            // Agrega un evento de escucha a los campos de entrada
                            fechaInput.addEventListener('change', recargarPagina);
                            horaInput.addEventListener('change', recargarPagina);

                          </script>
                        </form>

                  </div>
                </div>
              </div>
              <div class="col-md-8 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                      <h4 class="card-title mb-1" align="center">Interpretación</h4>
                    <br><br>
                    <p align="center"> <?php echo $interpretacion; ?> </p>
                </div>
              </div>
            </div>
          </div>
         

              <div class="row">
              <!--Imprimiendo los horarios ya configurados  -->
              <div class="col-lg-12 grid-margin stretch-card">
                  <div class="card">
                    <div class="card-body">
                      <h4 class="card-title" align="center">Automatizaciones configuradas</h4>
                      <br>
                      <p class="card-description">Una automatización es un horario programado para dispensar comida automaticamente. Todos los añadidos apareceran en la sigueinte tabla.</p>
                      <br>
                      <div class="table-responsive">
                        <table class="table" >
                          <thead  align="center"  style="color:white;">
                            <tr>
                              <th>Automatización</th>
                              <th>Eliminar</th>
                            </tr>
                          </thead>
                          <tbody>

                            <?php echo $cHorarios; ?>

                          </tbody>
                        </table>
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