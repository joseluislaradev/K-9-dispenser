<?php
if (isset($_POST['verificarWifi']) && isset($_POST['wifiSSID']) && isset($_POST['wifiPassword'])) {
    require_once("../../Modelo/modelo.php");

    $idDispensador = $_POST['verificarWifi'];
    $wifiSSIDDispensador = $_POST['wifiSSID'];
    $wifiPasswordDispensador = $_POST['wifiPassword'];

    //instancia y conexion con la BD
    $MySQL=instancia();
    $query = $MySQL->datosDispensador($idDispensador); //Funcion que devuelve los datos del dispensador

    $wifiSSID = $wifiPassword = null;

    foreach ($query as $filas) {
        $wifiSSID = $filas['wifiSSID'];
        $wifiPassword = $filas['wifiPassword'];
    }

    //Comprobando que la clave guardada sea igual a la enviada por el dispensador
    if($wifiSSIDDispensador != $wifiSSID || $wifiPasswordDispensador != $wifiPassword){
        // enviando respuesta a arduino para que actualice las claves de acceso wifi en sus memoria con la nueva
        echo "actualizarClaveWifi,";
        echo "$wifiSSID,";
        echo "$wifiPassword,";
    } else {
        // enviando mensaje a arduino de que las claves wifi son las mismas en la web y en su memoria
        echo "claveCorrecta,";
    }
}
?>