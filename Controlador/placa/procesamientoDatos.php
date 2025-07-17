<?php

if (isset($_POST['botonFisicoMotor']) || isset($_POST['checar_estadoDispensador'])) {
    require_once("../../Modelo/modelo.php");

    //Recibo el id dependiendo de que hizo la llamada
    if (isset($_POST['checar_estadoDispensador'])){
        $params = array (
            "idDispensador" => $_POST['checar_estadoDispensador'],
            "comidaDisponibleActual" => $_POST['comidaDisponibleActual'],
            "comidaPlatoActual" => $_POST['comidaPlatoActual']	
        );
        } else if (isset($_POST['botonFisicoMotor'])){
            $params = array (
                "idDispensador" => $_POST['botonFisicoMotor'],
                "comidaDisponibleActual" => $_POST['comidaDisponibleActual'],
                "comidaPlatoActual" => $_POST['comidaPlatoActual']	
            );   
        }

    //instancia y conexion con la BD
    $db = Database::getInstance();
    $conn = $db->getConnection();
    $sesion = new Modelo($conn);

    $count = $sesion->verificarSiExisteIdDispensador($params["idDispensador"]);

    if($count > 0){ //Si es mayor a 1 quiere decir que el id del dispensador se encontro en la base de datos por lo tanto existe
        //Consulta para verificar primero si existe alguna automatizacion programada en la hora actual
        $MySQL=instancia();
        $query = $MySQL->proximoHorarioDispensacion($params["idDispensador"]);  //Solo devuelve una variable y no un array
        
        $tipo = $recurrencia = $fechaAutomatizacion = null;

        foreach($query as $filas){
            $idHorario = $filas['idHorario'];    
            $tipo = $filas['Tipo'];
            $recurrencia = $filas['Recurrencia'];
            $fechaAutomatizacion = $filas ['AutomatizacionProxima'];
        }

        date_default_timezone_set('America/Mexico_City');
        $fechaHoraActual = date("Y-m-d H:i:00"); // Obtener la fecha actual sin segundos

        // Obtener solo la fecha y hora de la variable sin los segundos
        $fechaMySQLSinSegundos = substr($fechaAutomatizacion, 0, 16) . ":00"; //Al resultado del query le quito los segundos

        // Comparar las fechas sin los segundos
        if ($fechaHoraActual == $fechaMySQLSinSegundos) {
            //Si las fechas son iguales le debo responder al arduino para prender el boton pero antes cambiar la fecha de la automatizacion dependiendo del tipo que sea o eliminarla completamente

            $fechaProxima = null;

            if($tipo == "recurrente"){
                // Convertir la fecha en un objeto DateTime
                $fechaObjeto = new DateTime($fechaAutomatizacion);

                if($recurrencia == "diario"){
                    // Sumar un día a la fecha
                    $fechaObjeto->modify('+1 day');

                    // Obtener la fecha resultante
                    $fechaProxima = $fechaObjeto->format('Y-m-d');

                } else if($recurrencia == "semanas"){
                    // Sumar una semana a la fecha
                    $fechaObjeto->modify('+1 week');

                    // Obtener la fecha resultante
                    $fechaProxima = $fechaObjeto->format('Y-m-d');

                } else if($recurrencia == "diaMes"){
                    // Sumar un mes a la fecha
                    $fechaObjeto->modify('+1 month');

                    // Obtener la fecha resultante
                    $fechaProxima = $fechaObjeto->format('Y-m-d');
                } 
            }

            //Actualizando la fecha para la proxima dispensacion
            list ($valor, $error) = $sesion->consActualizarFechaAutomatizacion($idHorario, $fechaProxima);
            if(empty($valor)){
                if(!empty($error)) {
                    echo "error";
                }
            }

            /*Consultando el nombre del dispensador para guardarlo en la descripcion
            $nombreDispensador = $sesion->nombreDispensador($params["idDispensador"]);

            $titulo = "Automatización ejecutada";
            $descripcion = "Ejecutada por el dispensador de nombre: ". $nombreDispensador;

            //Agregando correo en la base de datos y enviandolo
            list ($valor, $error) = $sesion->agregarNotificacion($params["idDispensador"], $fechaHoraActual, $titulo, $descripcion);
            if(empty($valor)){
                if(!empty($error)) {
                    echo "error";
                }
            }
            */
    
            //Llamar a la funcion que actualiza el valor de estado en la base de datos y realiza la insercion de el ultimo registrado en la tabla infoDispensaciones
            list ($valor, $error) = $sesion->consDispensador($params["idDispensador"]);
            if(empty($valor)){
                if(!empty($error)) {
                    echo "error";
                }
            } else {
                echo "motor_is_on_automatizacionEncontrada";
            }



        // Si no se activo el boton fisico y no hay ninguna automatizacion programada sigo enviando datos a la base de datos y comprobando si esta prendido a apagado el servo
        } else if (isset($_POST['checar_estadoDispensador'])) {

            //Hago una consulta para ver si los datos son direferentes, si son iguales no los inserte, esto para ahorrar recursos y no estar insertando a cada rato los mismo valores
            $MySQL=instancia();
            $query = $MySQL->verificarCambios($params["idDispensador"]); 

            $ComidaDisponibleActual = $ComidaPlatoActual = $EstadoDispensador = null;

            foreach ($query as $filas) {
                $comidaDisponibleActual = $filas['ComidaDisponibleActual'];
                $comidaPlatoActual = $filas['ComidaPlatoActual'];
                $estadoDispensador = $filas ['EstadoDispensador'];
            }

            if($params["comidaDisponibleActual"] != $comidaDisponibleActual || $params["comidaPlatoActual"] != $comidaPlatoActual){
                //Llamar a la funcion que actualiza el valor en la base de datos
                list ($valor, $error) = $sesion->actualizarDatos($params);
                if(empty($valor)){
                    if(!empty($error)) {
                        echo "error";
                    }
                } 
            }

            if($estadoDispensador == 0){
                echo "motor_is_off";
            } else if($estadoDispensador == 1){
                echo "motor_is_on";
            }

        
        //Si se activo el boton fisico del dispensador queda activar el dispensador    
        } else if (isset($_POST['botonFisicoMotor'])) {
    
            //Hago una consulta para ver si los datos son direferentes, si son iguales no los inserte, esto para ahorrar recursos y no estar insertando a cada rato los mismo valores
            $MySQL=instancia();
            $query = $MySQL->verificarCambios($params["idDispensador"]); 

            $ComidaDisponibleActual = $ComidaPlatoActual = $EstadoDispensador = null;

            foreach ($query as $filas) {
                $comidaDisponibleActual = $filas['ComidaDisponibleActual'];
                $comidaPlatoActual = $filas['ComidaPlatoActual'];
                $estadoDispensador = $filas ['EstadoDispensador'];
            }

            if($params["comidaDisponibleActual"] != $comidaDisponibleActual || $params["comidaPlatoActual"] != $comidaPlatoActual){
                //Llamar a la funcion que actualiza el valor en la base de datos
                list ($valor, $error) = $sesion->actualizarDatos($params);
                if(empty($valor)){
                    if(!empty($error)) {
                        echo "error";
                    }
                } 
            }
            
            //Llamar a la funcion que actualiza el valor en la base de datos
            list ($valor, $error) = $sesion->consDispensador($params["idDispensador"]);
            if(empty($valor)){
                if(!empty($error)) {
                    echo "error";
                }
            } else {
                echo "motor_is_on_botonFisico";
            }
        }
    } else if($count <= 0){ //Si es menor o igual a 0 quiere decir que nuestro id dispensador no existe en la base de datos, eso quiere decir que se elimino desde formatear en la pagina u otra cuestion
        echo "dispensadorFormateado";
    }

}

?>