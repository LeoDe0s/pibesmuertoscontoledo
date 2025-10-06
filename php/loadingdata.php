<?php
// Incluye la conexión a la base de datos
$host = "localhost";
$user = "root";  // Cambia por tu usuario
$password = ""; // Cambia por tu contraseña
$dbname = "biblioteca_isft38"; // Tu base de datos

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("<h2>Error de conexión: " . $conn->connect_error . "</h2>");
}
$conn->set_charset("utf8mb4");

// Función para insertar un libro (ahora con imagen)
function insertarLibro($titulo, $autor, $categoria, $editorial, $cantidad, $isbn, $imagen_path) {
    global $conn;
    $sql = "INSERT INTO libros (titulo, autor, categoria, editorial, cantidad, isbn, imagen) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssiss", $titulo, $autor, $categoria, $editorial, $cantidad, $isbn, $imagen_path);
    if ($stmt->execute()) {
        return $conn->insert_id;
    } else {
        return false;
    }
    $stmt->close();
}

$message = "";
$mostrarModal = false;

// Procesar formulario
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cargar_libro'])) {
    $mostrarModal = true; // para abrir modal en caso de error o para confirmar
    
    // Verificar que todos los campos necesarios existan
    if (
        isset($_POST['titulo_libro']) && isset($_POST['autor']) && isset($_POST['categoria']) &&
        isset($_POST['editorial']) && isset($_POST['cantidad_disponible']) && isset($_POST['isbn'])
    ) {
        $titulo_libro = $_POST['titulo_libro'];
        $autor = $_POST['autor'];
        $categoria = $_POST['categoria'];
        $editorial = $_POST['editorial'];
        $cantidad_disponible = intval($_POST['cantidad_disponible']);
        $isbn = $_POST['isbn'];

        // Procesar imagen si fue subida
        $imagen_path = null;
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == UPLOAD_ERR_OK) {
            $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'gif'];
            $nombreArchivo = $_FILES['imagen']['name'];
            $tmpArchivo = $_FILES['imagen']['tmp_name'];
            $ext = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));

            if (in_array($ext, $extensionesPermitidas)) {
                $nombreNuevo = uniqid('img_') . '.' . $ext;
                $carpetaDestino = __DIR__ . '/uploads/';
                if (!file_exists($carpetaDestino)) {
                    mkdir($carpetaDestino, 0755, true);
                }
                $rutaDestino = $carpetaDestino . $nombreNuevo;

                if (move_uploaded_file($tmpArchivo, $rutaDestino)) {
                    $imagen_path = 'uploads/' . $nombreNuevo;
                } else {
                    $message .= "<p style='color:red;'>Error al subir la imagen.</p>";
                }
            } else {
                $message .= "<p style='color:red;'>Formato de imagen no permitido. Usa jpg, jpeg, png o gif.</p>";
            }
        }

        // Insertar libro solo si no hubo error en imagen
        if (strpos($message, 'error') === false) {
            $libro_id = insertarLibro($titulo_libro, $autor, $categoria, $editorial, $cantidad_disponible, $isbn, $imagen_path);
            if ($libro_id) {
                $message .= "<p style='color:green;'>Libro cargado correctamente. ID: " . $libro_id . "</p>";
                $mostrarModal = false; // cerrar modal porque fue exitoso
            } else {
                $message .= "<p style='color:red;'>Error al insertar el libro en la base de datos.</p>";
            }
        }
    } else {
        $message .= "<p style='color:red;'>Por favor, complete todos los campos requeridos.</p>";
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Cargar Libro en Biblioteca</title>
<style>
body {
    font-family: Arial, sans-serif;
    background-color: #f2f2f2;
    margin: 0; padding: 0;
}
h1 {
    background-color: #4CAF50;
    color: white;
    padding: 15px;
    text-align: center;
}
.container {
    max-width: 900px;
    margin: 20px auto;
    background-color: #fff;
    padding: 20px 40px;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}
h2 {
    color: #333;
}
form {
    display: flex;
    flex-direction: column;
}
label {
    margin-top: 15px;
    font-weight: bold;
}
input[type=text], input[type=number], input[type=file] {
    padding: 8px;
    margin-top: 5px;
    border: 1px solid #ccc;
    border-radius: 4px;
}
input[type=submit] {
    margin-top: 20px;
    padding: 12px;
    background-color: #4CAF50;
    border: none;
    color: white;
    font-size: 16px;
    border-radius: 4px;
    cursor: pointer;
}
input[type=submit]:hover {
    background-color: #45a049;
}
.message {
    margin-top: 20px;
    font-weight: bold;
}
.modal {
    position: fixed;
    top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0,0,0,0.5);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}
.modal-content {
    background: white;
    padding: 20px 30px;
    border-radius: 10px;
    width: 450px;
    max-width: 90%;
    max-height: 90%;
    overflow-y: auto;
}
.modal-buttons {
    margin-top: 20px;
    text-align: right;
}
.btn-cerrar {
    background: #ccc;
    border: none;
    padding: 8px 12px;
    margin-left: 10px;
    border-radius: 5px;
    cursor: pointer;
}
</style>
</head>
<body>

<h1>Libros</h1>

<div class="container">
    <button onclick="abrirModal()" style="padding:10px 20px; font-size:16px; cursor:pointer;">Cargar Libro</button>
    <div class="message"><?php echo $message; ?></div>
</div>

<!-- MODAL PARA CARGAR LIBRO -->
<div class="modal" id="modalCargarLibro">
    <div class="modal-content">
        <h2>Cargar Nuevo Libro</h2>
        <form method="post" enctype="multipart/form-data" action="">
            <input type="hidden" name="cargar_libro" value="1" />

            <label for="titulo_libro">Título:</label>
            <input type="text" id="titulo_libro" name="titulo_libro" required>

            <label for="autor">Autor:</label>
            <input type="text" id="autor" name="autor" required>

            <label for="categoria">Categoría:</label>
            <input type="text" id="categoria" name="categoria" required>

            <label for="editorial">Editorial:</label>
            <input type="text" id="editorial" name="editorial" required>

            <label for="cantidad_disponible">Cantidad Disponible:</label>
            <input type="number" id="cantidad_disponible" name="cantidad_disponible" min="1" required>

            <label for="isbn">ISBN:</label>
            <input type="text" id="isbn" name="isbn" required>

            <label for="imagen">Imagen (jpg, png, gif):</label>
            <input type="file" id="imagen" name="imagen" accept=".jpg,.jpeg,.png,.gif">

            <div class="modal-buttons">
                <input type="submit" value="Guardar Libro" />
                <button type="button" class="btn-cerrar" onclick="cerrarModal()">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<script>
function abrirModal() {
    document.getElementById('modalCargarLibro').style.display = 'flex';
}
function cerrarModal() {
    document.getElementById('modalCargarLibro').style.display = 'none';
}

// Abrir modal automáticamente si hay mensajes (error o éxito)
<?php if ($mostrarModal): ?>
    abrirModal();
<?php endif; ?>
</script>

</body>
</html>