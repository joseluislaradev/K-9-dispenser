<?php

@session_start();
require_once("../Modelo/modelo.php");


if($_POST){

    if(!(empty($_FILES["foto"]))){
            $directorioFoto = "assets/images/faces-dispenser/";
            $archivoFoto = $directorioFoto . basename($_FILES["foto"]["name"]); // Es la direccion del archivo
            $size = $_FILES["foto"]["size"];
            // Obtener la extensión del archivo
            $extension = ".".pathinfo($_FILES["foto"]["name"], PATHINFO_EXTENSION);
  
            if($size > 10000000){
              echo '<script language="javascript">alert("Hubo un error al subir tu foto, debe pesar menos de 10000kB");</script>';
            }else{  
                    // se validó el archivo correctamente
                    if(move_uploaded_file($_FILES["foto"]["tmp_name"], "../public_html/".$archivoFoto)){
                      rename("../public_html/".$archivoFoto, "../public_html/".$directorioFoto."FotoDispensador-Id".$_SESSION["idDispensador"]."$extension"); // Si todo esta bien ponemos un nombre al archivo para identificarlo cuando lo subamos
  
                      $archivoFoto = $directorioFoto."FotoDispensador-Id".$_SESSION["idDispensador"]."$extension";//Le asignamos la ruta con el nuevo nombre


                        //REDIMENCIONAR IMAGEN

                        $rutaImagenOriginal = "../public_html/".$directorioFoto."FotoDispensador-Id".$_SESSION["idDispensador"]."$extension";

                        // Definir el nuevo tamaño deseado
                        $nuevoAncho = 300; // Anchura deseada en píxeles
                        $nuevoAlto = 250; // Altura deseada en píxeles

                        // Crear una imagen a partir del archivo original
                        if ($extension == '.jpg' || $extension == '.jpeg') {
                            $imagenOriginal = imagecreatefromjpeg($rutaImagenOriginal);
                        } elseif ($extension == '.png') {
                            $imagenOriginal = imagecreatefrompng($rutaImagenOriginal);
                        } elseif ($extension == '.gif') {
                            $imagenOriginal = imagecreatefromgif($rutaImagenOriginal);
                        }

                        // Obtener las dimensiones actuales de la imagen original
                        $anchoOriginal = imagesx($imagenOriginal);
                        $altoOriginal = imagesy($imagenOriginal);

                        // Crear una nueva imagen con las dimensiones deseadas
                        $nuevaImagen = imagecreatetruecolor($nuevoAncho, $nuevoAlto);

                        // Redimensionar la imagen original a la nueva imagen
                        imagecopyresampled($nuevaImagen, $imagenOriginal, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $anchoOriginal, $altoOriginal);

                        // Guardar la nueva imagen en un archivo
                        $rutaNuevaImagen = "../public_html/".$directorioFoto."FotoDispensador-Id".$_SESSION["idDispensador"]."$extension";;
                        imagejpeg($nuevaImagen, $rutaNuevaImagen, 90);

                        // Liberar memoria
                        imagedestroy($imagenOriginal);
                        imagedestroy($nuevaImagen);

                      $params = array(
                        "ruta" => $archivoFoto,
                        "idDispensador" => $_SESSION["idDispensador"] 
                    );

                        //instancia y conexion con la BD
                        $db = Database::getInstance();
                        $conn = $db->getConnection();
                        $sesion = new Modelo($conn);
                    
                        //Llamar a la funcion 'agregaUsuario' que se encuentra en el modelo
                        list ($valor, $error) = $sesion->actualizarFotoDispensador($params);
                        if(empty($valor)){
                            if(!empty($error)) {
                                $_SESSION["error"] = $error;
                            }
                        } else {
                            if($_SESSION["rutaDispensador"] != $archivoFoto && $_SESSION["rutaDispensador"] != ""){
                                $nombreImagen = "../public_html/".$_SESSION["rutaDispensador"];
                                if (file_exists($nombreImagen)) {
                                    unlink($nombreImagen);
                                } 
                            }

                            echo "<script> alert ('La foto se actualizo exitosamente'); 
                            window.location.href='../public_html/perfilDispensador.php';
                            </script>";
                        }

                    }else{
                      echo '<script language="javascript">alert("Hubo un error al subir tu foto. Intentalo de nuevo");</script>';
                    }
            }
    } else {
        $params = array(
            "nombreDispensador" => $_POST['nombreDispensador'],
            "raza" => $_POST['raza'], 
            "peso" => $_POST['peso'],
            "descripcion" => $_POST["descripcion"],
            "idDispensador" => $_SESSION["idDispensador"]
        );
            
            //instancia y conexion con la BD
            $db = Database::getInstance();
            $conn = $db->getConnection();
            $sesion = new Modelo($conn);
        
            //Llamar a la funcion 'agregaUsuario' que se encuentra en el modelo
            list ($valor, $error) = $sesion->actualizarDispensador($params);
            if(empty($valor)){
                if(!empty($error)) {
                    $_SESSION["error"] = $error;
                }
            } else {
                echo "<script> alert ('Los datos se actualizaron exitosamente'); 
                window.location.href='../public_html/perfilDispensador.php';
                </script>";
            }
    }
    
} 



?>