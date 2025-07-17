<?php
    require_once(__DIR__ . '/../Modelo/modelo.php');
	//require_once($_SERVER['DOCUMENT_ROOT']."/k9dispenser/Modelo/modelo.php");
	
	function instancia(){
		$db=Database::getInstance();
		$conn = $db->getConnection();
		$MySQL = new Modelo($conn);
		return $MySQL;
	}

	//Funcion para obtener todos los datos que se muestran en la seccion de configuracion sobre el usuario
	function perfil($id){
		$MySQL=instancia();
		$query = $MySQL->datosPerfil($id);

		$nombre = $ape1 = $ape2 = $telefono = $correo = $edad = $usuario = null;

		foreach ($query as $filas) {
			$nombre = $filas['Nombre'];
			$ape1 = $filas['ApellidoM'];
			$ape2 = $filas ['ApellidoP'];
			$correo = $filas['Correo'];
			$tipoUsuario = $filas ['TipoUsuario'];
			$imagen = $filas ['ImagenPerfil'];

		}
		$result[] = $nombre;
		$result[] = $ape1;
		$result[] = $ape2;
		$result[] = $correo;
		$result[] = $tipoUsuario;
		$result[] = $imagen;

		return $result;
	}


	//Funcion para eliminar una imagen guardada si existe alguna en la ruta guardada
	function eliminarImagen($id){
		$MySQL=instancia();

		@session_start();

		$nombreImagen = $_SESSION["ruta"];

		if (file_exists($nombreImagen)) {
			if (unlink($nombreImagen)) {
				//Llamar a la funcion 'agregaUsuario' que se encuentra en el modelo
				list ($valor, $error) = $MySQL->eliminarRuta($id);
				if(empty($valor)){
					if(!empty($error)) {
					$_SESSION["error"] = $error;
					}
				} else {
					$_SESSION["ruta"] = "";					
					echo "<script> alert ('La imagen se elimino exitosamente'); 
					window.location.href='../public_html/configuracion.php';
					</script>";
				}
			} else {
				echo "<script> alert ('No se pudo eliminar la imagen. Intente de nuevo mas tarde'); 
				window.location.href='../public_html/configuracion.php';
				</script>";			}
		} else {
			echo "La imagen no existe.";
		}
	}

		//Funcion para eliminar una imagen guardada si existe alguna en la ruta guardada
		function eliminarImagenDispensador($idDispensador){
			$MySQL=instancia();
	
			@session_start();
	
			$nombreImagen = $_SESSION["rutaDispensador"];
	
			if (file_exists($nombreImagen)) {
				if (unlink($nombreImagen)) {
					//Llamar a la funcion 'agregaUsuario' que se encuentra en el modelo
					list ($valor, $error) = $MySQL->eliminarRutaDispensador($idDispensador);
					if(empty($valor)){
						if(!empty($error)) {
						$_SESSION["error"] = $error;
						}
					} else {	
						$_SESSION["rutaDispensador"] = "";									
						echo "<script> alert ('La imagen se elimino exitosamente'); 
						window.location.href='../public_html/perfilDispensador.php';
						</script>";
					}
				} else {
					echo "<script> alert ('No se pudo eliminar la imagen. Intente de nuevo mas tarde'); 
					window.location.href='../public_html/perfilDispensador.php';
					</script>";			}
			} else {
				echo "La imagen no existe.";
			}
		}
	
	//Funcion que crea la tabla donde se indican todo los dispensadores disponibles para el usuario
	function C_dispensadores($id){

		$MySQL = instancia();
		$dispensadores = $MySQL->consDispensadores($id);

		$tblPro = "";
		foreach($dispensadores as $dispensador){  
			$tblPro .= "<tr class = 'text-center' align='center'>\n";
			$tblPro .= "<td> <img class='img-sm rounded-circle' src='". (($dispensador['FotoPerro'] != "") ? $dispensador['FotoPerro'] : "assets/images/perritoElegante.png") ."' alt='' srcset=''> </td>\n";
			$tblPro .= "<td>". $dispensador['NombreDispensador']. "</td>\n";
			$tblPro .= "<td>". $dispensador['ComidaDisponibleActual']. "g</td>\n";
			$tblPro .= "<td>". $dispensador['ComidaPlatoActual']. "g</td>\n";
			$tblPro .= "<td>". $dispensador['PesoDispensar']. "g</td>\n";
			$tblPro .= "<td class='text-center'> 
				<form action='indexUsuario.php' method='post' enctype='multipart/form-data' id='motor'> 
					<input type='number' name='idDispensador' id='idDispensador' value='". $dispensador['idDispensador']."' hidden>
					<input id='submit_button' type='submit' name='activarMotor' value='".(($dispensador['EstadoDispensador'] == 1) ? "DISPENSANDO" : "DISPENSAR")."' class='d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm' ".(($dispensador['EstadoDispensador'] == 1) ? "disabled" : "")."/>
				</form>
			</td>\n";
			$tblPro .= "<td>".(($dispensador['EstadoDispensador'] == 1)?"<img width = '30' src='assets/images/circuloVerde.png'>":"<img width = '30' src='assets/images/circuloRojo.png'>"). "</td>\n";
			$tblPro .= "<td> 
				<form action='dispensador.php' method='post' enctype='multipart/form-data' id='motor'> 
					<input type='number' name='idDispensador' id='idDispensador' value='". $dispensador['idDispensador']."' hidden>
					<input id='submit_button' type='submit' value='Ver mas' class='d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm'/>
				</form>
			</td>\n";

			$tblPro .= "</tr>\n";
		}
		return $tblPro;
	}


	//Funcion intermediaria para actualizar el estado del dispensador al clickear el boton de dispensar
	function actualizarEstadoDispensador($idDispensador){

		//instancia y conexion con la BD
		$db = Database::getInstance();
		$conn = $db->getConnection();
		$sesion = new Modelo($conn);

		list ($valor, $error) = $sesion->consDispensador($idDispensador);
		if(empty($valor)){
			if(!empty($error)) {
				$_SESSION["error"] = $error;
			}
		} else {
			@session_start();
			if($_SESSION['pagina']=="indexUsuario"){
				echo "<script> window.location.href='../public_html/indexUsuario.php'; </script>";
			} else if($_SESSION['pagina']=="indexDispensador") {
				echo "<script> window.location.href='../public_html/dispensador.php; </script>";
			} if($_SESSION['pagina']=="analisis") {
				echo "<script> window.location.href='../public_html/analisis.php; </script>";
			} if($_SESSION['pagina']=="automatizacion") {
				echo "<script> window.location.href='../public_html/automatizacion.php; </script>";
			} if($_SESSION['pagina']=="configuracionDispensador") {
				echo "<script> window.location.href='../public_html/configuracionDispensador.php; </script>";
			} if($_SESSION['pagina']=="perfilDispensador") {
				echo "<script> window.location.href='../public_html/perfilDispensador.php; </script>";
			} if($_SESSION['pagina']=="historial") {
				echo "<script> window.location.href='../public_html/historial.php; </script>";
			}
		}

	}


	//Funcion para obtener todos los datos de un dispensador que utilizare en la pantalla dispensador.php
	function dispensadores($idDispensador){
		$MySQL=instancia();
		$query = $MySQL->datosDispensador($idDispensador);

		$nombre = $raza = $peso = $foto = $descripcion = $comidaDisponibleActual = $comidaPlatoActual = $pesoDispensar = $estado = $wifiSSID = $wifiPassword = null;

		foreach ($query as $filas) {
			$nombreDispensador = $filas['NombreDispensador'];
			$raza = $filas['RazaPerro'];
			$peso = $filas ['PesoPerro'];
			$fotoDispensador = $filas['FotoPerro'];
			$descripcion = $filas ['DescripcionDispensador'];
			$comidaDisponibleActual = $filas ['ComidaDisponibleActual'];
			$comidaPlatoActual = $filas ['ComidaPlatoActual'];
			$pesoDispensar = $filas ['PesoDispensar'];
			$estado = $filas ['EstadoDispensador'];
			$wifiSSID = $filas ['wifiSSID'];
			$wifiPassword = $filas ['wifiPassword'];
		}

		$result[] = $nombreDispensador;
		$result[] = $raza;
		$result[] = $peso;
		$result[] = $fotoDispensador;
		$result[] = $descripcion;
		$result[] = $comidaDisponibleActual;
		$result[] = $comidaPlatoActual;
		$result[] = $pesoDispensar;
		$result[] = $estado;
		$result[] = $wifiSSID;
		$result[] = $wifiPassword;

		return $result;
	}



	//Funcion que crea la tabla donde se indican todo los horarios configurados para cierto dispensador
	function C_horarios($idDispensador){

		$MySQL = instancia();
		$horarios = $MySQL->consHorarios($idDispensador);

		$tblPro = "";
		foreach($horarios as $horario){  
			$tblPro .= "<tr class = 'text-center' align='center'>\n";
			$tblPro .= "<td>". $horario['Interpretacion']. "</td>\n";
			$tblPro .= "<td class='text-center'> 
				<form action='../Controlador/eliminarHorario.php' method='post' enctype='multipart/form-data' id='motor'> 
					<input type='number' name='idHorario' id='idHorario' value='". $horario['idHorario']."' hidden>
					<input class='btn btn-danger btn-fw' id='submit_button' type='submit' name='horario' value='ELIMINAR' />
				</form>
			</td>\n";
			$tblPro .= "</tr>\n";
		}
		return $tblPro;
	}


	//Funcion que crea la tabla donde se indican toda la informacion de las dispensaciones hechas por el usuario, es decir el Historial
	function C_dispensaciones($idDispensador){

		$MySQL = instancia();
		$dispensaciones = $MySQL->consInfoDispensacion($idDispensador);
		
		$tblPro = "";
		foreach($dispensaciones as $dispensacion){  
			$tblPro .= "<tr class = 'text-center' align='center'>\n";
			$tblPro .= "<td>". $dispensacion['Fecha']. "</td>\n";
			$tblPro .= "<td>". $dispensacion['Hora']. "</td>\n";
			$tblPro .= "<td>". $dispensacion['PesoDispensar']. "g</td>\n";
			$tblPro .= "<td>". $dispensacion['ProximidadComida']. "g</td>\n";
			$tblPro .= "<td>". $dispensacion['PesoSobrante']. "g</td>\n";
			$tblPro .= "<td class='text-center'> 
				<form action='../Controlador/eliminarInfoDispensacion.php' method='post' enctype='multipart/form-data' id='motor'> 
					<input type='number' name='eliminarUno' id='idInfoDispensacion' value='". $dispensacion['idInfoDispensacion']."' hidden>
					<input class='btn btn-danger btn-fw' id='submit_button' type='submit' name='activarMotor' value='ELIMINAR' />
				</form>
			</td>\n";
			$tblPro .= "</tr>\n";
		}
		return $tblPro;
	}

	//Hace consulta para conocer el horario o automatizacion mas proxima
	function c_proximoHorario($idDispensador){
		$MySQL=instancia();
		$query = $MySQL->proximoHorario($idDispensador);

		$fecha = $hora = null;

		foreach ($query as $filas) {
			$fecha = $filas['FechaProgramada'];
			$hora = $filas['HoraProgramada'];
		}

		$result[] = $fecha;
		$result[] = $hora;

		return $result;
	}

	//Hace consulta para conocer el horario o automatizacion mas proxima
	function c_ultimaDispensacion($idDispensador){
		$MySQL=instancia();
		$query = $MySQL->ultimaDispensacion($idDispensador);

		$fecha = $hora = null;

		foreach ($query as $filas) {
			$fecha = $filas['Fecha'];
			$hora = $filas['Hora'];
		}

		$result[] = $fecha;
		$result[] = $hora;

		return $result;
	}


	function formatearDispensadores($cantidad, $id){
		//instancia y conexion con la BD
		$db = Database::getInstance();
		$conn = $db->getConnection();
		$sesion = new Modelo($conn);

		if($cantidad == 1){
			$idDispensador = $id;
			//Llamar a la funcion 'agregaUsuario' que se encuentra en el modelo
			list ($valor, $error) = $sesion->formatearDispensador($idDispensador);
		} else if($cantidad == 2){
			$idUsuario = $id;
			//Llamar a la funcion 'agregaUsuario' que se encuentra en el modelo
			list ($valor, $error) = $sesion->formatearDispensadores($idUsuario);
		}

		if(empty($valor)){
			if(!empty($error)) {
				$_SESSION["error"] = $error;
			}
		} else {
			echo "<script> alert ('PROCESO DE FORMATEO COMPLETADO'); 
			window.location.href='../public_html/indexUsuario.php';
			</script>";
		}
	}


	function eliminarCuenta($id){
		//instancia y conexion con la BD
		$db = Database::getInstance();
		$conn = $db->getConnection();
		$sesion = new Modelo($conn);

		list ($valor, $error) = $sesion->eliminarCuentaUsuario($id);

		if(empty($valor)){
			if(!empty($error)) {
				$_SESSION["error"] = $error;
			}
		} else {
			echo "<script> alert ('La cuenta se elimino exitosamente'); 
			window.location.href='../public_html/login.html';
			</script>";
		}
	}

	//Cmabia el peso que dispensa el dispensador solicitado desde la pgaina dispensador.php
	function cambiarPeso($peso, $idDispensador){
		//instancia y conexion con la BD
		$db = Database::getInstance();
		$conn = $db->getConnection();
		$sesion = new Modelo($conn);

		list ($valor, $error) = $sesion->cambiarPesoBD($peso, $idDispensador);

		if(empty($valor)){
			if(!empty($error)) {
				$_SESSION["error"] = $error;
			}
		} else {
			echo "<script> alert ('EL peso se actualizo exitosamente'); 
			window.location.href='../public_html/dispensador.php';
			</script>";
		}
	}


	function guardarToken($uniqueId, $id){
		//instancia y conexion con la BD
		$db = Database::getInstance();
		$conn = $db->getConnection();
		$sesion = new Modelo($conn);

		list ($valor, $error) = $sesion->guardarTokenUnico($uniqueId, $id);
		@session_start();

		if(empty($valor)){
			if(!empty($error)) {
				$_SESSION["error"] = $error;
			}
		} else {
			$para = $_SESSION["correo"];
			$asunto = "Cambio de contraseña";

			// Mensaje con formato HTML
			$mensaje = "<html>
			<head>
			<title>Cambio de contraseña</title>
			<style>
			body {
			  font-family: Arial, sans-serif;
			  background-color: #f5f5f5;
			  color: #333;
			}
			h1 {
			  color: #0066cc;
			}
			p {
			  margin-bottom: 10px;
			}
			a {
			  color: #0099ff;
			  text-decoration: none;
			}
			a:hover {
			  text-decoration: underline;
			}
		  	</style>
			</head>
			<body>
			<h1 align='center'>¡Solicitud de cambio de contraseña!</h1>
			<p>Haz clic en el siguiente enlace para cambiar tu contraseña.</p>
			<p><a href='https://k9dispenser.000webhostapp.com/public_html/cambiarContrasena.php?token=$uniqueId'>Confirmar correo electrónico</a></p>
			</body>
			</html>";

			// Establece las cabeceras para que el correo sea HTML
			$headers = "From: perezlarajose5@gmail.com\r\n";
			$headers = "Reply-to: perezlarajose5@gmail.com\r\n";
			$headers .= "MIME-Version: 1.0\r\n";
			$headers .= "Content-Type: text/html; charset=UTF-8\r\n";

			// Envía el correo electrónico
			if (mail($para, $asunto, $mensaje, $headers)) {
				echo "<script> alert ('El correo ha sido enviado');
				 window.location.href='../public_html/pantallaEspera.php'; 
				 </script>";
			} else {
				echo "<script> alert ('Ocurrio un error al enviar el correo. Por favor intenta nuevamente.'); 
				window.location.href='../public_html/configuracionA.php';
				</script>";			
			}
		}
	}

	function existeToken($id){
		//instancia y conexion con la BD
		$db = Database::getInstance();
		$conn = $db->getConnection();
		$sesion = new Modelo($conn);

		$existeToken = $sesion->existeTokenUnico($id);

		return $existeToken;
	}
	



	


	


	
?>

