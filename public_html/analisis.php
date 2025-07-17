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
            $_SESSION['pagina'] = "analisis";
            actualizarEstadoDispensador($idDispensador);
        } 

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
            
              <div class="col-lg-6 grid-margin stretch-card">
                <div class="card"> 
                  <div class="card-body"  align="center">
                    <h4 class="card-title" style="display:inline;">COMIDA SOBRANTE</h4>
                    <br><br>
                    <p class="card-description">En esta gráfica se puede observar cuanta comida no se comio la mascota antes de la siguiente dispensación, es decir, se puede observar la comida sobrante en cada dispensación.</p>
                    <div class="dropdown" align="right">
                      <select  class="btn btn-outline-info dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" id="rangoTiempo1" onchange="actualizarGrafica1()">
                        <option class="dropdown-item" value="ultimoDia">Último día</option>
                        <option class="dropdown-item" value="ultimaSemana">Última semana</option>
                        <option class="dropdown-item" value="ultimoMes">Último mes</option>
                        <option class="dropdown-item" value="ultimos6Meses">Últimos 6 meses</option>
                        <option class="dropdown-item" value="ultimoAnio">Último año</option>
                      </select>
                    </div> 
                    <br>
                    <canvas id="grafica1" style="height:250px"></canvas>
                  </div>
                </div>
              </div>

              <div class="col-lg-6 grid-margin stretch-card">
                <div class="card"> 
                  <div class="card-body"  align="center">
                    <h4 class="card-title" style="display:inline;">COMIDA CONSUMIDA</h4>
                    <br><br>
                    <p class="card-description">En esta gráfica se puede observar cuanta comida fue consumida según el tiempo seleccionado.</p>
                    <div class="dropdown" align="right">
                      <select  class="btn btn-outline-info dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" id="rangoTiempo2" onchange="actualizarGrafica2()">
                        <option class="dropdown-item" value="ultimoDia">Último día</option>
                        <option class="dropdown-item" value="ultimaSemana">Última semana</option>
                        <option class="dropdown-item" value="ultimoMes">Último mes</option>
                        <option class="dropdown-item" value="ultimos6Meses">Últimos 6 meses</option>
                        <option class="dropdown-item" value="ultimoAnio">Último año</option>
                      </select>
                    </div> 
                    <br>
                    <canvas id="grafica2" style="height:250px"></canvas>
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
    <script src="assets/vendors/js/vendor.bundle.base.js"></script>
    <!-- endinject -->
    <!-- Plugin js for this page -->
    <script src="assets/vendors/chart.js/Chart.min.js"></script>
    <!-- End plugin js for this page -->


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

  <!-- Grafica 1 -->
  <script>
    function actualizarGrafica1() {
        // Obtener el valor seleccionado por el usuario
        var rangoTiempo1 = document.getElementById("rangoTiempo1").value;
        var rangoTiempo2 = document.getElementById("rangoTiempo2").value;

        // Guardar el valor seleccionado en el almacenamiento local
        localStorage.setItem('rangoSeleccionado1', rangoTiempo1);
        localStorage.setItem('rangoSeleccionado2', rangoTiempo2);
        
        // Utiliza AJAX para enviar el valor seleccionado al servidor
        fetch('../Controlador/obtenerDatos.php?rango=' + rangoTiempo1 + "&grafica=1")
            .then(response => response.json())
            .then(data => {
              // Parsear el JSON para convertirlo en un array de objetos

              // Ahora puedes reemplazar los datos estáticos con los datos dinámicos en el objeto 'data'
              var labels = data.map(d => d.NumeroRegistro);
              var valores = data.map(d => d.PesoSobrante);

              // Crear la gráfica con Chart.js
              const ctx = document.getElementById('grafica1').getContext('2d');
              var lineChart = new Chart(ctx, {
                type: 'line',
                data: {
                  labels: labels,
                  datasets: [{
                    label: 'Gramos de comida sobrante',
                    data: valores,
                    backgroundColor: [
                      'rgba(255, 99, 132, 0.2)',
                      'rgba(54, 162, 235, 0.2)',
                      'rgba(255, 206, 86, 0.2)',
                      'rgba(75, 192, 192, 0.2)',
                      'rgba(153, 102, 255, 0.2)',
                      'rgba(255, 159, 64, 0.2)'
                    ],
                    borderColor: [
                      'rgba(255,99,132,1)',
                      'rgba(54, 162, 235, 1)',
                      'rgba(255, 206, 86, 1)',
                      'rgba(75, 192, 192, 1)',
                      'rgba(153, 102, 255, 1)',
                      'rgba(255, 159, 64, 1)'
                    ],
                    borderWidth: 1
                  }]
                },
                options: {
                  // Opciones de configuración de la gráfica
                }
              });
            })
            .catch(error => console.error('Error al obtener los datos:', error));
        }
      


       function actualizarGrafica2() {
        // Obtener el valor seleccionado por el usuario
        var rangoTiempo1 = document.getElementById("rangoTiempo1").value;
        var rangoTiempo2 = document.getElementById("rangoTiempo2").value;

        // Guardar el valor seleccionado en el almacenamiento local
        localStorage.setItem('rangoSeleccionado1', rangoTiempo1);
        localStorage.setItem('rangoSeleccionado2', rangoTiempo2);
        
        // Utiliza AJAX para enviar el valor seleccionado al servidor
        fetch('../Controlador/obtenerDatos.php?rango=' + rangoTiempo2 + "&grafica=2")
            .then(response => response.json())
            .then(data => {
              // Parsear el JSON para convertirlo en un array de objetos

              // Ahora puedes reemplazar los datos estáticos con los datos dinámicos en el objeto 'data'
              var labels = data.map(d => d.tiempo);
              var valores = data.map(d => d.suma_de_pesos);

              // Crear la gráfica con Chart.js
              const ctx = document.getElementById('grafica2').getContext('2d');
              var lineChart = new Chart(ctx, {
                type: 'bar',
                data: {
                  labels: labels,
                  datasets: [{
                    label: 'Gramos de comida consumida',
                    data: valores,
                    backgroundColor: [
                      'rgba(255, 99, 132, 0.2)',
                      'rgba(54, 162, 235, 0.2)',
                      'rgba(255, 206, 86, 0.2)',
                      'rgba(75, 192, 192, 0.2)',
                      'rgba(153, 102, 255, 0.2)',
                      'rgba(255, 159, 64, 0.2)'
                    ],
                    borderColor: [
                      'rgba(255,99,132,1)',
                      'rgba(54, 162, 235, 1)',
                      'rgba(255, 206, 86, 1)',
                      'rgba(75, 192, 192, 1)',
                      'rgba(153, 102, 255, 1)',
                      'rgba(255, 159, 64, 1)'
                    ],
                    borderWidth: 1
                  }]
                },
                options: {
                  // Opciones de configuración de la gráfica
                }
              });
            })
            .catch(error => console.error('Error al obtener los datos:', error));
        }
      


      function cargarValorSeleccionado() {
        // Obtener el valor seleccionado almacenado previamente
        var rangoSeleccionado1 = localStorage.getItem('rangoSeleccionado1');
        var rangoSeleccionado2 = localStorage.getItem('rangoSeleccionado2');

        // Si hay un valor almacenado, establecerlo como opción seleccionada en el select
        if (rangoSeleccionado1 ||rangoSeleccionado2) {
            document.getElementById("rangoTiempo1").value = rangoSeleccionado1;
            document.getElementById("rangoTiempo2").value = rangoSeleccionado2;
        }
      }

      // Cargar la gráfica inicial con el rango de tiempo "última hora"
      cargarValorSeleccionado();
      actualizarGrafica1();
      actualizarGrafica2();

  </script>


  </body>
</html>