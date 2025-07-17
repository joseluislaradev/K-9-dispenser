<?php
    require_once(__DIR__ . '/../Controlador/conexion.php');
    require_once(__DIR__ . '/../Controlador/controlador.php');
	//require_once($_SERVER['DOCUMENT_ROOT']."/k9dispenser/Controlador/conexion.php");
	//require_once($_SERVER['DOCUMENT_ROOT']."/k9dispenser/Controlador/controlador.php");	
	
	class Modelo{
		
		private $conn;
		
		function __construct( $conexion ){
			$this->conn = $conexion;
		}

		// Funcion que agrega datos de registro
		function agregaUsuario( $params ){
			$error = "";
			$valor = "";
			$nombre = $params["nombre"];
			$ape1 = $params["apellidoP"];
			$ape2 = $params["apellidoM"];
			$email = $params["email"];
			$pass = $params["contraseña"];
			$intentosSesion = 0;
			$tipo = 1;
			$imagenPerfil = "";
			$token = null;

			$query = "INSERT INTO `usuarios` VALUES (NULL, '$nombre', '$ape1', '$ape2', '$email', '$pass', '$imagenPerfil', '$intentosSesion', '$tipo', '$token');";
			
			if(!empty( $nombre ) && !empty( $ape1 ) && !empty( $email ) ){
				if(!($this->conn->query($query))){
				$error = 'Ocurrio un error ejecutando el query [' . $this->conn->error . ']';
				} else {
				$valor = $this->conn->affected_rows;
				}
			}

			$resul[] = $valor;
			$resul[] = $error;
			return $resul;
		}

		//Verifica que el email con el que se esta registrando no exista en la BD
		function verificarCorreo($email){
			$error = "";
			$valor = "";

			$query = "SELECT Correo FROM usuarios WHERE Correo = '$email'";
			$resultado = mysqli_query($this->conn, $query);
   
			if(!$resultado){
				$error = 'MySQL Error: '. mysqli_connect_error();
			} else {
				if(!empty($resultado) && mysqli_num_rows($resultado) > 0){
					$valor = "ok";
				}
			}

			$resul[] = $valor;
			$resul[] = $error;
			return $resul;
		}


		//Comprueba el usuario y contraseña para logearse
		function validaUsuario ($params){
			$error = "";
			$valor = "";
			$email = $params["user"]; 
			$pass = $params["pass"];
   

			$query0 = "SELECT Contraseña FROM usuarios WHERE Correo = '$email'";
			$resultado0 = mysqli_query($this->conn, $query0);

			if(!$resultado0){
				$error = 'MySQL Error: '. mysqli_connect_error();
			} else {
				// Si hay contenido en la contraseña se ejecuta todo
				if(!empty($resultado0) && mysqli_num_rows($resultado0) > 0){
					while($row = mysqli_fetch_array($resultado0))
					{
						$hash = $row['Contraseña'];
					}
					//Se comprueba que la contraseña encriptada sea igual a la que ingreso para loguearse
					if (password_verify($pass, $hash)) {
						
						//Se hace la consulta para sacar todos los datos del email
						$query = "SELECT *FROM usuarios WHERE Correo = '".$email."';";
						$resultado = mysqli_query($this->conn, $query);
			
						//Si hay datos se recuperan los datos que necesitamos
						if(mysqli_num_rows($resultado)!= 0){
							$valor = "OK";
							@session_start();
							$_SESSION["logueado"] = TRUE;
							while($row = mysqli_fetch_array($resultado)){
								$_SESSION["id"] = $row['idUsuario'];
								$_SESSION["nombre"] = $row['Nombre'];
								$_SESSION["correo"] = $row['Correo'];
								$_SESSION["tipoUsuario"] = $row['TipoUsuario'];
								$_SESSION['ruta'] = $row['ImagenPerfil'];
								$_SESSION["idDispensador"] = 0;
							}
						}

						//Si ya se inicio sesion se reinicia el contador de intentosSesionFallidos

						$contador = 0;
						$query2 = "UPDATE `usuarios` SET `IntentosSesionFallidos` = $contador WHERE `usuarios`.`Correo` = '$email';";
					
						if(!($this->conn->query($query2))){
							$error = 'Ocurrio un error ejecutando el query [' . $this->conn->error . ']';
						}
							

					// Si la contraseña esta mal se actualiza el valor de intentos de sesion fallidos sumandole 1
					} else {
							
						//Consultando valor actual de intentos fallidos
						$query1 = "SELECT IntentosSesionFallidos FROM usuarios WHERE Correo = '$email'";
						$resultado1 = mysqli_query($this->conn, $query1);
						if(!$resultado1){
							$error = 'MySQL Error: '. mysqli_connect_error();
						} else {
							//En caso de que si exista ese correo...
							if(!empty($resultado1) && mysqli_num_rows($resultado1) > 0){
								//Actualizando el valor de intentos fallidos
								while($row = mysqli_fetch_array($resultado1))
								{
									$IntentosSesionFallidos = $row['IntentosSesionFallidos'];
								}
								$intentoNuevo = $IntentosSesionFallidos + 1;
								$query2 = "UPDATE `usuarios` SET `IntentosSesionFallidos` = $intentoNuevo WHERE `usuarios`.`Correo` = '$email';";
					
								if(!($this->conn->query($query2))){
									$error = 'Ocurrio un error ejecutando el query [' . $this->conn->error . ']';
								}
							}
						}
					}  
				}
			}
   
		   $resul[] = $valor;
		   $resul[] = $error;
		   return $resul; 
	   }


	   //Comprueba que el usuario no supere los 3 intentos de inicio de sesion fallidos
	   function usuarioBloqueadoVerificar($params){
			$error = "";
			$valor = "";
			$email = $params["user"]; 

			//Consultando valor actual de intentos fallidos
			$query1 = "SELECT IntentosSesionFallidos FROM usuarios WHERE Correo = '$email'";
			$resultado1 = mysqli_query($this->conn, $query1);
			if(!$resultado1){
				$error = 'MySQL Error: '. mysqli_connect_error();
			} else {
				//En caso de que si exista ese correo...
				if(!empty($resultado1) && mysqli_num_rows($resultado1) > 0){
				//Actualizando el valor de intentos fallidos
					while($row = mysqli_fetch_array($resultado1)){
						$IntentosSesionFallidos = $row['IntentosSesionFallidos'];
					}
					if($IntentosSesionFallidos > 3){
						$valor = "bloqueada";
					}
				}
			}

			$resul[] = $valor;
			$resul[] = $error;
			return $resul; 
	   }


	   //Esta funcion devuelve todo de la tabla
	   function datosPerfil($id){
		$query = "SELECT *FROM usuarios WHERE idUsuario = " .$id;
		$resultado = mysqli_query($this->conn, $query);
		if(!$resultado){
			$error = 'MySQL Error: '. mysqli_connect_error();
		}
		return $resultado;
	   }


	   //Funcion para actualizar los datos normales del usuario
	   function actualizarUsuario($params){
			$error = "";
			$valor = "";
			$nombre = $params["nombre"];
			$ape1 = $params["ape1"];
			$ape2 = $params["ape2"];
			$email = $params["email"];
			$id = $params["id"];

			$query = "UPDATE `usuarios` SET `Nombre` = '$nombre', `ApellidoM` = '$ape1', `ApellidoP` = '$ape2', `Correo` = '$email' WHERE `usuarios`.idUsuario = '$id';";

			if(!($this->conn->query($query))){
				$error = 'Ocurrio un error ejecutando el query [' . $this->conn->error . ']';
			} else {
				$valor = 'Todo wuay';
			}
			
			$resul[] = $valor;
			$resul[] = $error;
			return $resul;
		}

	   //Funcion para actualizar la foto del usuario
	   function actualizarFotoUsuario($params){
		$error = "";
		$valor = "";
		$ruta = $params["ruta"];
		$id = $params["id"];

		$query = "UPDATE `usuarios` SET `ImagenPerfil` = '$ruta' WHERE `usuarios`.idUsuario = '$id';";

		if(!($this->conn->query($query))){
			$error = 'Ocurrio un error ejecutando el query [' . $this->conn->error . ']';
		} else {
			$valor = 'Todo wuay';
		}
		
		$resul[] = $valor;
		$resul[] = $error;
		return $resul;
	}


	//Funcion para eliminar la ruta de la imagen en la base de datos
	function eliminarRuta($id){
		$error = "";
		$valor = "";
		$ruta = "";

		$query = "UPDATE `usuarios` SET `ImagenPerfil` = '$ruta' WHERE `usuarios`.idUsuario = '$id';";

		if(!($this->conn->query($query))){
			$error = 'Ocurrio un error ejecutando el query [' . $this->conn->error . ']';
		} else {
			$valor = 'Todo wuay';
		}
		
		$resul[] = $valor;
		$resul[] = $error;
		return $resul;
	}

	//Funcion para eliminar la ruta de la imagen en la base de datos
	function eliminarRutaDispensador($idDispensador){
		$error = "";
		$valor = "";
		$ruta = "";

		$query = "UPDATE `dispensadores` SET `FotoPerro` = '$ruta' WHERE `dispensadores`.idDispensador = '$idDispensador';";

		if(!($this->conn->query($query))){
			$error = 'Ocurrio un error ejecutando el query [' . $this->conn->error . ']';
		} else {
			$valor = 'Todo wuay';
		}
		
		$resul[] = $valor;
		$resul[] = $error;
		return $resul;
	}

	//Funcion para consultar todos los datos de la tabla dispensadores de cierto usuario y llenar la tabla de inicio
	function consDispensadores($id){
		$sql = "SELECT *FROM `dispensadores` WHERE idUsuario = $id";
		$resDispensadores = mysqli_query($this->conn, $sql);
		if(!$resDispensadores){
			$error = 'MySQL Error: '. mysqli_connect_error();
		}
		return $resDispensadores;
	}

	//Funcion para consultar todos los datos de cierto dispensador
	function datosDispensador($idDispensador){
		$sql = "SELECT *FROM `dispensadores` WHERE idDispensador = $idDispensador";
		$resDispensadores = mysqli_query($this->conn, $sql);
		if(!$resDispensadores){
			$error = 'MySQL Error: '. mysqli_connect_error();
		}
		return $resDispensadores;
	}

	//Funcion para consultar todos los datos de la tabla horarios de cierto dispensador
	function consHorarios($idDispensador){
		$sql = "SELECT *FROM `horarios` WHERE idDispensador = $idDispensador";
		$resDispensadores = mysqli_query($this->conn, $sql);
		if(!$resDispensadores){
			$error = 'MySQL Error: '. mysqli_connect_error();
		}
		return $resDispensadores;
	}
	
	//Funcion para consultar y actualizar el estado del dispensador cuando se clickea el boton
	function consDispensador($idDispensador){
		$error = "";
		$valor = "";
		$sql = "SELECT *FROM `dispensadores` WHERE idDispensador = $idDispensador";
		$resDispensadores = mysqli_query($this->conn, $sql);
		if(!$resDispensadores){
			$error = 'MySQL Error: '. mysqli_connect_error();
			return $resDispensadores;
		}

		while($row = mysqli_fetch_array($resDispensadores)){
			if($row['EstadoDispensador'] == 0){
				//Se hace la consulta para sacar los valores que actuales de los dispensadores para guardar la info de la dipensacion en la tabla infoDispensaciones
				$query1 = "SELECT ComidaDisponibleActual, ComidaPlatoActual, PesoDispensar FROM dispensadores WHERE idDispensador = '".$idDispensador."';";
				$resultado = mysqli_query($this->conn, $query1);	
				//Si hay datos se recuperan los datos que necesitamos
				if(mysqli_num_rows($resultado)!= 0){
					while($row = mysqli_fetch_array($resultado)){
						$comidaDisponibleActual = $row['ComidaDisponibleActual'];
						$comidaPlatoActual = $row['ComidaPlatoActual'];
						$pesoDispensar = $row['PesoDispensar'];
					}
				}
				date_default_timezone_set('America/Mexico_City');
				// Obtener la fecha actual en una variable con formato "Y-m-d"
				$fechaActual = date("Y-m-d");

				// Obtener la hora actual en una variable con formato "H:i:s"
				$horaActual = date("H:i:s");

				//Insertando todos los datos necesarios en la tabla infoDispensaciones 
				$query2 = "INSERT INTO `infodispensaciones` VALUES (NULL, '$fechaActual', '$horaActual', '$pesoDispensar', '$comidaDisponibleActual', '$comidaPlatoActual', '$idDispensador');";
		
				if(!empty( $idDispensador) ){
					if(!($this->conn->query($query2))){
					$error = 'Ocurrio un error ejecutando el query [' . $this->conn->error . ']';
					}
					$valor = $this->conn->affected_rows;
				}

				$query = "UPDATE `dispensadores` SET EstadoDispensador = 1 WHERE idDispensador = $idDispensador";	

			  }else if($row['EstadoDispensador'] == 1) {
				$query = "UPDATE `dispensadores` SET EstadoDispensador = 0 WHERE idDispensador = $idDispensador";		
			  }
		}

		//Ejecutar el query que actualiza el estado del dispensador
		if(!($this->conn->query($query))){
			$error = 'Ocurrio un error ejecutando el query [' . $this->conn->error . ']';
		} else {
			$valor = 'Todo wuay';
		}
		
		$resul[] = $valor;
		$resul[] = $error;
		return $resul;
	  
	}



	// Funcion que agrega datos de registro a un nuevo horario o automatizacion
	function guardarHorarioBD( $params ){
		$error = "";
		$valor = "";
		$idDispensador = $params["idDispensador"];
		$interpretacion = $params["interpretacion"];
		$tipo = $params["tipo"];
		$recurrencia = $params["recurrencia"];
		$fecha = $params["fecha"];
		$hora = $params["hora"];

		$query = "INSERT INTO `horarios` VALUES (NULL, '$tipo', '$recurrencia', '$fecha', '$hora', '$interpretacion', '$idDispensador');";
		
		if(!empty( $idDispensador) ){
			if(!($this->conn->query($query))){
			$error = 'Ocurrio un error ejecutando el query [' . $this->conn->error . ']';
			}
			$valor = $this->conn->affected_rows;
		}

		$resul[] = $valor;
		$resul[] = $error;
		return $resul;
	}

	//Funcion que elimina un horario o progrmaacion
	function eliminarHorario($idHorario){
		$error = "";
		$valor = "";
		$query = "DELETE FROM horarios WHERE idHorario = " .$idHorario;
		
		if(!($this->conn->query($query))){
			$error = 'Ocurrio un error ejecutando el query [' . $this->conn->error . ']';
		} else {
			$valor = $this->conn->affected_rows;
		}

		$resul[] = $valor;
		$resul[] = $error;
		return $resul;

	   }


	//Funcion para consultar todos los datos de la tabla infodispensaciones de cierto dispensador
	function consInfoDispensacion($idDispensador){
		$sql = "SELECT *FROM `infodispensaciones` WHERE idDispensador = $idDispensador ORDER BY Fecha DESC, Hora DESC";
		$resDispensadores = mysqli_query($this->conn, $sql);
		if(!$resDispensadores){
			$error = 'MySQL Error: '. mysqli_connect_error();
		}
		return $resDispensadores;
	}


	//Funcion para actualizar los datos normales de el perfil de los dispensadores
		function actualizarDispensador($params){
		$error = "";
		$valor = "";
		$nombreDispensador = $params["nombreDispensador"];
		$raza = $params["raza"];
		$peso = $params["peso"];
		$descripcion = $params["descripcion"];
		$idDispensador = $params["idDispensador"];

		$query = "UPDATE `dispensadores` SET `NombreDispensador` = '$nombreDispensador', `RazaPerro` = '$raza', `PesoPerro` = '$peso', `DescripcionDispensador` = '$descripcion' WHERE `dispensadores`.idDispensador = '$idDispensador';";

		if(!($this->conn->query($query))){
			$error = 'Ocurrio un error ejecutando el query [' . $this->conn->error . ']';
		} else {
			$valor = 'Todo wuay';
		}
		
		$resul[] = $valor;
		$resul[] = $error;
		return $resul;
	}


	//Funcion para actualizar la foto del dispensador
	function actualizarFotoDispensador($params){
		$error = "";
		$valor = "";
		$ruta = $params["ruta"];
		$idDispensador = $params["idDispensador"];

		$query = "UPDATE `dispensadores` SET `FotoPerro` = '$ruta' WHERE `dispensadores`.idDispensador = '$idDispensador';";

		if(!($this->conn->query($query))){
			$error = 'Ocurrio un error ejecutando el query [' . $this->conn->error . ']';
		} else {
			$valor = 'Todo wuay';
		}
		
		$resul[] = $valor;
		$resul[] = $error;
		return $resul;
	}


	//Permite eliminar solo un registro de infoDispensacion
	function eliminarInfoDispensacion($idInfoDispensacion){
		$error = "";
		$valor = "";
		$query = "DELETE FROM `infodispensaciones` WHERE `idInfoDispensacion` = " .$idInfoDispensacion;
		
		if(!($this->conn->query($query))){
			$error = 'Ocurrio un error ejecutando el query [' . $this->conn->error . ']';
		} else {
			$valor = $this->conn->affected_rows;
		}

		$resul[] = $valor;
		$resul[] = $error;
		return $resul;

	}


	//Permite eliminar todos los registros de infodispensaciones de cierto dispensador
	function eliminarTodoInfoDispensacion($idDispensador){
		$error = "";
		$valor = "";
		$query = "DELETE FROM `infodispensaciones` WHERE `idDispensador` = " .$idDispensador;
		
		if(!($this->conn->query($query))){
			$error = 'Ocurrio un error ejecutando el query [' . $this->conn->error . ']';
		} else {
			$valor = $this->conn->affected_rows;
		}

		$resul[] = $valor;
		$resul[] = $error;
		return $resul;

	}


	function obtenerDatosGraficas($rangoTiempo, $grafica, $idDispensador){
		$error = "";
		$valor = "";
		date_default_timezone_set('America/Mexico_City');

		if($grafica == 1){
			// Según el valor del rango de tiempo, modificar la consulta SQL
			switch ($rangoTiempo) {
				case 'ultimoDia':
					// Consulta para obtener los datos de la última hora
					$sql = "SELECT ROW_NUMBER() OVER (ORDER BY idInfoDispensacion ASC) AS NumeroRegistro, PesoSobrante FROM infodispensaciones WHERE CONCAT(Fecha, ' ', Hora) >= DATE_SUB(NOW(), INTERVAL 1 DAY) AND idDispensador = '$idDispensador';";
					break;
				case 'ultimaSemana':
					// Consulta para obtener los datos de la última hora
					$sql = "SELECT ROW_NUMBER() OVER (ORDER BY idInfoDispensacion ASC) AS NumeroRegistro, PesoSobrante FROM infodispensaciones WHERE CONCAT(Fecha, ' ', Hora) >= DATE_SUB(NOW(), INTERVAL 1 WEEK) AND idDispensador = '$idDispensador';";
					break;
				case 'ultimoMes':
					// Consulta para obtener los datos del ultimo mes
					$sql = "SELECT ROW_NUMBER() OVER (ORDER BY idInfoDispensacion ASC) AS NumeroRegistro, PesoSobrante FROM infodispensaciones WHERE CONCAT(Fecha, ' ', Hora) >= DATE_SUB(NOW(), INTERVAL 1 MONTH) AND idDispensador = '$idDispensador';";
					break;
				case 'ultimos6Meses':
					// Consulta para obtener los datos de¿ los ultimos 6 meses año
					$sql = "SELECT ROW_NUMBER() OVER (ORDER BY idInfoDispensacion ASC) AS NumeroRegistro, PesoSobrante FROM infodispensaciones WHERE CONCAT(Fecha, ' ', Hora) >= DATE_SUB(NOW(), INTERVAL 6 MONTH) AND idDispensador = '$idDispensador';";
					break;
				case 'ultimoAnio':
					// Consulta para obtener los datos del último año
					$sql = "SELECT ROW_NUMBER() OVER (ORDER BY idInfoDispensacion ASC) AS NumeroRegistro, PesoSobrante FROM infodispensaciones WHERE CONCAT(Fecha, ' ', Hora) >= DATE_SUB(NOW(), INTERVAL 1 YEAR) AND idDispensador = '$idDispensador';";
					break;
				default:
					// Valor por defecto (última hora)
					$sql = "SELECT ROW_NUMBER() OVER (ORDER BY idInfoDispensacion ASC) AS NumeroRegistro, PesoSobrante FROM infodispensaciones WHERE CONCAT(Fecha, ' ', Hora) >= DATE_SUB(NOW(), INTERVAL 1 DAY) AND idDispensador = '$idDispensador';";
					break;
			}
	
		} else if($grafica == 2){
			// Según el valor del rango de tiempo, modificar la consulta SQL
			switch ($rangoTiempo) {
				case 'ultimoDia':
					// Consulta para obtener los datos de la última hora
					$sql = "SELECT HOUR(CONCAT(Fecha, ' ', Hora)) AS tiempo, SUM(PesoDispensar) AS suma_de_pesos FROM infodispensaciones WHERE Fecha >= DATE_SUB(CURDATE(), INTERVAL 1 DAY) AND idDispensador = '$idDispensador' GROUP BY tiempo;";
					break;
				case 'ultimaSemana':
					// Consulta para obtener los datos de la última hora
					$sql = "SELECT DATE(Fecha) AS tiempo, SUM(PesoDispensar) AS suma_de_pesos FROM infodispensaciones WHERE Fecha >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND idDispensador = '$idDispensador' GROUP BY tiempo;";
					break;
				case 'ultimoMes':
					// Consulta para obtener los datos del ultimo mes
					$sql = "SELECT DATE(Fecha) AS tiempo, SUM(PesoDispensar) AS suma_de_pesos FROM infodispensaciones WHERE Fecha >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) AND idDispensador = '$idDispensador' GROUP BY tiempo;";
					break;
				case 'ultimos6Meses':
					// Consulta para obtener los datos de¿ los ultimos 6 meses año
					$sql = "SELECT DATE_FORMAT(Fecha, '%Y-%m') AS tiempo, SUM(PesoDispensar) AS suma_de_pesos FROM infodispensaciones WHERE Fecha >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) AND idDispensador = '$idDispensador' GROUP BY tiempo;";
					break;
				case 'ultimoAnio':
					// Consulta para obtener los datos del último año
					$sql = "SELECT DATE_FORMAT(Fecha, '%Y-%m') AS tiempo, SUM(PesoDispensar) AS suma_de_pesos FROM infodispensaciones WHERE Fecha >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH) AND idDispensador = '$idDispensador' GROUP BY tiempo;";
					break;
				default:
					// Valor por defecto (última hora)
					$sql = "SELECT HOUR(CONCAT(Fecha, ' ', Hora)) AS tiempo, SUM(PesoDispensar) AS suma_de_pesos FROM infodispensaciones WHERE Fecha >= DATE_SUB(CURDATE(), INTERVAL 1 DAY) AND idDispensador = '$idDispensador' GROUP BY tiempo;";
					break;
			}
	
		} else if($grafica == 3){

	
		} else if($grafica == 4){

	
		} 

		$resultado = mysqli_query($this->conn, $sql);
		if(!$resultado){
			$error = 'MySQL Error: '. mysqli_connect_error();
		} else {
			$valor = "ok";
		}

		// Preparar un array para almacenar los datos
		$datos = array();
	
		// Obtener los datos de la consulta y almacenarlos en el array
		while ($fila = mysqli_fetch_assoc($resultado)) {
			$datos[] = $fila;
		}
	
		// Convertir el array en formato JSON
		$jsonData = json_encode($datos);

		$resul[] = $jsonData;
		$resul[] = $valor;
		$resul[] = $error;
		return $resul;
	}

	//Selecciona todo del horario mas proximo par mostrarlo en la pantalla de resumen de cierto dispensador
	function proximoHorario($idDispensador){
		$sql = "SELECT *FROM `horarios` WHERE idDispensador = $idDispensador ORDER BY FechaProgramada ASC, HoraProgramada ASC LIMIT 1";
		$resDispensadores = mysqli_query($this->conn, $sql);
		if(!$resDispensadores){
			$error = 'MySQL Error: '. mysqli_connect_error();
		}
		return $resDispensadores;
	}

	//Selecciona todo del horario mas proximo
	function ultimaDispensacion($idDispensador){
		$sql = "SELECT *FROM `infodispensaciones` WHERE idDispensador = $idDispensador ORDER BY Fecha DESC, Hora DESC LIMIT 1";
		$resDispensadores = mysqli_query($this->conn, $sql);
		if(!$resDispensadores){
			$error = 'MySQL Error: '. mysqli_connect_error();
		}
		return $resDispensadores;
	}


	function formatearDispensador($idDispensador){
		$error = "";
		$valor = "";
		$query = "DELETE FROM `dispensadores` WHERE `idDispensador` = " .$idDispensador;
		
		if(!($this->conn->query($query))){
			$error = 'Ocurrio un error ejecutando el query [' . $this->conn->error . ']';
		} else {
			$valor = $this->conn->affected_rows;
		}

		$resul[] = $valor;
		$resul[] = $error;
		return $resul;
	}

	function formatearDispensadores($idUsuario){
		$error = "";
		$valor = "";
		$query = "DELETE FROM `dispensadores` WHERE `idUsuario` = " .$idUsuario;
		
		if(!($this->conn->query($query))){
			$error = 'Ocurrio un error ejecutando el query [' . $this->conn->error . ']';
		} else {
			$valor = $this->conn->affected_rows;
		}

		$resul[] = $valor;
		$resul[] = $error;
		return $resul;
	}


	function eliminarCuentaUsuario($idUsuario){
		$error = "";
		$valor = "";
		$query = "DELETE FROM `usuarios` WHERE `idUsuario` = " .$idUsuario;
		
		if(!($this->conn->query($query))){
			$error = 'Ocurrio un error ejecutando el query [' . $this->conn->error . ']';
		} else {
			$valor = $this->conn->affected_rows;
		}

		$resul[] = $valor;
		$resul[] = $error;
		return $resul;
	}


	function cambiarPesoBD($peso, $idDispensador){
		$error = "";
		$valor = "";

		$query = "UPDATE `dispensadores` SET `PesoDispensar` = '$peso' WHERE `dispensadores`.idDispensador = '$idDispensador';";

		if(!($this->conn->query($query))){
			$error = 'Ocurrio un error ejecutando el query [' . $this->conn->error . ']';
		} else {
			$valor = 'Todo wuay';
		}
		
		$resul[] = $valor;
		$resul[] = $error;
		return $resul;
	}


	function actualizarWifi($params){
		$error = "";
		$valor = "";
		$wifiSSID = $params["wifiSSID"];
		$wifiPassword = $params["wifiPassword"];
		$idDispensador = $params["idDispensador"];

		$query = "UPDATE `dispensadores` SET `wifiSSID` = '$wifiSSID', `wifiPassword` = '$wifiPassword' WHERE `dispensadores`.idDispensador = '$idDispensador';";

		if(!($this->conn->query($query))){
			$error = 'Ocurrio un error ejecutando el query [' . $this->conn->error . ']';
		} else {
			$valor = 'Todo wuay';
		}
		
		$resul[] = $valor;
		$resul[] = $error;
		return $resul;
	}

	function cambiarContraseña($contrasena, $id){
		$error = "";
		$valor = "";

		$query = "UPDATE `usuarios` SET `Contraseña` = '$contrasena', `Token` = NULL WHERE `usuarios`.idUsuario = '$id';";

		if(!($this->conn->query($query))){
			$error = 'Ocurrio un error ejecutando el query [' . $this->conn->error . ']';
		} else {
			$valor = 'Todo wuay';
		}
		
		$resul[] = $valor;
		$resul[] = $error;
		return $resul;
	}


	function guardarTokenUnico($uniqueId, $id){
		$error = "";
		$valor = "";

		$query = "UPDATE `usuarios` SET `Token` = '$uniqueId' WHERE `usuarios`.idUsuario = '$id';";

		if(!($this->conn->query($query))){
			$error = 'Ocurrio un error ejecutando el query [' . $this->conn->error . ']';
		} else {
			$valor = 'Todo wuay';
		}
		
		$resul[] = $valor;
		$resul[] = $error;
		return $resul;
	}


    //Selecciona el token de cierto id 
    function existeTokenUnico($id){
        $token = 0;
        $sql = "SELECT COUNT(Token) as count FROM `usuarios` WHERE idUsuario = '$id'";
        $resDispensadores = mysqli_query($this->conn, $sql);
		if(!$resDispensadores){
			$error = 'MySQL Error: '. mysqli_connect_error();
		}
		if ($resDispensadores) {
			// Verificar si hay resultados (al menos una fila)
			if (mysqli_num_rows($resDispensadores) > 0) {
				// Obtener la fila como un array asociativo
				$fila = mysqli_fetch_assoc($resDispensadores);
		
				// Acceder al valor del campo 'Token'
				$count = $fila['count'];
			}
		}
        
        return $count;
    }

	//Selecciona el nombre de cierto dispensador
	function nombreDispensador($idDispensador){
		$token = NULL;
		$sql = "SELECT NombreDispensador FROM `dispensadores` WHERE idDispensador = $idDispensador";
		$resDispensadores = mysqli_query($this->conn, $sql);
		if(!$resDispensadores){
			$error = 'MySQL Error: '. mysqli_connect_error();
		}
		if ($resDispensadores) {
			// Verificar si hay resultados (al menos una fila)
			if (mysqli_num_rows($resDispensadores) > 0) {
				// Obtener la fila como un array asociativo
				$fila = mysqli_fetch_assoc($resDispensadores);
		
				// Acceder al valor del campo 'Token'
				$token = $fila['NombreDispensador'];
			}
		}
		return $token;
	}

	//Esta funcion se usa para registrar una nueva notificacion
	function agregarNotificacion($idDispensador, $fechaHoraActual, $titulo, $descripcion){
		$error = "";
		$valor = "";

		$query = "INSERT INTO notificaciones VALUES (NULL, '$titulo', '$descripcion', '$fechaHoraActual' , '$idDispensador');"; 
		
		if( !empty($idUsuario )){
			if(!($this->conn->query($query))){
				$error = 'Ocurrio un error ejecutando el query [' . $this->conn->error . ']';
			} else {
				$valor = $this->conn->affected_rows;
				$idDispensador = $conn->insert_id;
			}
		}

		$resul[] = $valor;
		$resul[] = $error;
		return $resul;
	}




	/* -----------------------------------------------------------------------------------------------------
	---------------COMIENZA CONSULTAS REALIZADAS RELACIONADAS CON LA PLACA NODE ESP32-----------------------
	------------------------------------------------------------------------------------------------------*/

	//Esta funcion se utiliza para insertar un nuevo dispensador en la base de datos y retornar el id del registro
	function registrarDispensador($params){
		$error = "";
		$valor = "";
		$idUsuario = $params["idUsuario"];
		$wifiSSID = $params["wifiSSID"];
		$wifiPassword = $params["wifiPassword"];
		$idDispensador = 0;

		$query = "INSERT INTO dispensadores (idDispensador, wifiSSID, wifiPassword, idUsuario) VALUES (NULL, '$wifiSSID', '$wifiPassword', '$idUsuario');"; 
		
		if( !empty($idUsuario )){
			if(!($this->conn->query($query))){
				$error = 'Ocurrio un error ejecutando el query [' . $this->conn->error . ']';
			} else {
				$valor = $this->conn->affected_rows;
				$idDispensador = $this->conn->insert_id;
			}
		}

		$resul[] = $idDispensador;
		$resul[] = $valor;
		$resul[] = $error;
		return $resul;
	}

	//Se manda a llamar para actualizar el estado del dispensador de prendido a apagado
	function apagadoAuto($idDispensador){
		$error = "";
		$valor = "";

		//Consultado el estado del dispensador para saber si efectivamente esta prendido el servo
		$sql = "SELECT EstadoDispensador FROM `dispensadores` WHERE idDispensador = $idDispensador";
		$resDispensadores = mysqli_query($this->conn, $sql);

		if(!$resDispensadores){
			$error = 'MySQL Error: '. mysqli_connect_error();
		} else { // Si se ejecuto correctamente la consulta anterior y devolvio resultado

			if ($resDispensadores) {
				// Verificar si hay resultados (al menos una fila)
				if (mysqli_num_rows($resDispensadores) > 0) {
					// Obtener la fila como un array asociativo
					$fila = mysqli_fetch_assoc($resDispensadores);
			
					// Acceder al valor del campo 'Token'
					$estado = $fila['EstadoDispensador'];
				}
			}

			//Si esta prendido el servo, se actualiza la BD para apagarlo
			if($estado == 1){ 
				$query = "UPDATE dispensadores SET EstadoDispensador = 0 WHERE idDispensador = $idDispensador;";

				if(!($this->conn->query($query))){
					$error = 'Ocurrio un error ejecutando el query [' . $this->conn->error . ']';
				} else {
					$valor = 'Todo wuay'; // Si se ejecuto bien la actualizacion manda la variable valor con contenido
				}
			} else if($estado == 0){ 
				$valor = 'Todo wuay'; // Si se ejecuto bien la actualizacion manda la variable valor con contenido			
			}

		}

		$resul[] = $valor;
		$resul[] = $error;
		return $resul;
	}

	//Devuelve los valores del plato y de la caja que alamcena la comida para saber si esta enviando los mismos datos la placa y no actualizar para no conusmir recursos
	function verificarCambios($idDispensador){
		$sql = "SELECT ComidaDisponibleActual, ComidaPlatoActual, EstadoDispensador FROM `dispensadores` WHERE idDispensador = $idDispensador";
		$resDispensadores = mysqli_query($this->conn, $sql);
		if(!$resDispensadores){
			$error = 'MySQL Error: '. mysqli_connect_error();
		}
		return $resDispensadores;
	}

	//Funcion para actualizar los datos que envia constantemente el dispensador es decir los del plato y la comida que almacena
	function actualizarDatos($params){
		$error = "";
		$valor = "";
		$comidaDisponibleActual = $params["comidaDisponibleActual"];
		$comidaPlatoActual = $params["comidaPlatoActual"];
		$idDispensador = $params["idDispensador"];

		$query = "UPDATE `dispensadores` SET `ComidaDisponibleActual` = '$comidaDisponibleActual', `ComidaPlatoActual` = '$comidaPlatoActual' WHERE `dispensadores`.idDispensador = '$idDispensador';";

		if(!($this->conn->query($query))){
			$error = 'Ocurrio un error ejecutando el query [' . $this->conn->error . ']';
		} else {
			$valor = 'Todo wuay';
		}
		
		$resul[] = $valor;
		$resul[] = $error;
		return $resul;
	}

	//Selecciona la fecha y hora de la proxima dispensacion mas cercana 
	function proximoHorarioDispensacion($idDispensador){
		$sql = "SELECT idHorario, Tipo, Recurrencia, CONCAT(FechaProgramada, ' ', HoraProgramada) AS AutomatizacionProxima FROM `horarios` WHERE idDispensador = $idDispensador ORDER BY AutomatizacionProxima ASC LIMIT 1";
		$resDispensadores = mysqli_query($this->conn, $sql);
		if(!$resDispensadores){
			$error = 'MySQL Error: '. mysqli_connect_error();
		}
		return $resDispensadores;
	}

	function consActualizarFechaAutomatizacion($idHorario, $fechaProxima){
		$error = "";
		$valor = "";

		if($fechaProxima == null){
			$query = "DELETE FROM `horarios` WHERE `idHorario` = " .$idHorario;
		} else {
			$query = "UPDATE `horarios` SET `FechaProgramada` = '$fechaProxima' WHERE `horarios`.idHorario = '$idHorario';";
		}

		if(!($this->conn->query($query))){
			$error = 'Ocurrio un error ejecutando el query [' . $this->conn->error . ']';
		} else {
			$valor = 'Todo wuay';
		}
		
		$resul[] = $valor;
		$resul[] = $error;
		return $resul;
	}


	function verificarSiExisteIdDispensador($idDispensador){
		$sql = "SELECT COUNT(*) as count FROM `dispensadores` WHERE idDispensador = $idDispensador";
		$resDispensadores = mysqli_query($this->conn, $sql);
		if(!$resDispensadores){
			$error = 'MySQL Error: '. mysqli_connect_error();
		}
		if ($resDispensadores) {
			// Verificar si hay resultados (al menos una fila)
			if (mysqli_num_rows($resDispensadores) > 0) {
				// Obtener la fila como un array asociativo
				$fila = mysqli_fetch_assoc($resDispensadores);
		
				// Acceder al valor del campo 'Token'
				$count = $fila['count'];
			}
		}
		return $count;
	}

	//Consulta que devuelve la cantidad de peso que se dispensara en cierta dispensacion
	function consultarCantidad($idDispensador){
		$error="";
		$valor="";
		$token = NULL;
		$sql = "SELECT PesoDispensar FROM `dispensadores` WHERE idDispensador = $idDispensador";
		$resDispensadores = mysqli_query($this->conn, $sql);
		if(!$resDispensadores){
			$error = 'MySQL Error: '. mysqli_connect_error();
		}
		if ($resDispensadores) {
			// Verificar si hay resultados (al menos una fila)
			if (mysqli_num_rows($resDispensadores) > 0) {
				// Obtener la fila como un array asociativo
				$fila = mysqli_fetch_assoc($resDispensadores);
		
				// Acceder al valor del campo 'Token'
				$cantidad = $fila['PesoDispensar'];
				$valor = "Hola todo bien";
			}
		}
		$resul[] = $cantidad;
		$resul[] = $valor;
		$resul[] = $error;
		return $resul;
	}



	   

    
}


?>