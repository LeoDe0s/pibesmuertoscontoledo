
<?php
$host = "localhost";
$usuario = "root";
$contraseña = "";
$basededatos = "isft_38";

$conn = new mysqli($host, $usuario, $contraseña, $basededatos);

if ($conn->connect_error){
    die("Conexión fallida: " . $conn->connect_error);
} else {
    echo "Conectado exitosamente a la base de datos";
}
?>

