<?php
// Conexión a la base de datos
$host = "localhost";
$user = "root";
$password = "";
$dbname = "biblioteca_isft38";

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// Solo aceptamos POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Recibimos y limpiamos los datos del formulario
    $id = intval($_POST['id']);
    $titulo = $conn->real_escape_string($_POST['titulo']);
    $autor = $conn->real_escape_string($_POST['autor']);
    $categoria = $conn->real_escape_string($_POST['categoria']);
    $isbn = $conn->real_escape_string($_POST['isbn']);
    $editorial = $conn->real_escape_string($_POST['editorial']);
    $cantidad = intval($_POST['cantidad']);

    // Preparar y ejecutar la consulta de actualización
    $sql = "UPDATE Libros SET titulo=?, autor=?, categoria=?, isbn=?, editorial=?, cantidad=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssi", $titulo, $autor, $categoria, $isbn, $editorial, $cantidad, $id);

    if ($stmt->execute()) {
        // Si todo salió bien, redirigimos al detalle del libro actualizado
        header("Location: libro.php?id=" . $id);
        exit;
    } else {
        echo "Error al actualizar el libro: " . $conn->error;
    }

    $stmt->close();
}

$conn->close();
?>
