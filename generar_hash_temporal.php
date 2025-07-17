<?php
// Escribe aquí la contraseña que quieres usar
$password_plana = 'k9admin';

// Este código genera el hash compatible
$hash = password_hash($password_plana, PASSWORD_DEFAULT);

// Esto mostrará el hash en pantalla
echo "El hash para la contraseña '<strong>" . $password_plana . "</strong>' es:<br><br>";
echo "<textarea rows='4' cols='80'>" . $hash . "</textarea>";
?>