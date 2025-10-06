<?php
// Conexión a la base de datos
$host = "localhost";
$user = "root";
$password = "";
$dbname = "biblioteca_isft38";

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("<h2>Error de conexión: " . $conn->connect_error . "</h2>");
}
$conn->set_charset("utf8mb4");

$mensajeExito = "";
$mensajeError = "";

// Función para obtener el nombre de la categoría del libro
function obtenerNombreCategoria($conn, $id_libro) {
    $stmt = $conn->prepare("SELECT c.nombre FROM libro_categorias lc JOIN categorias c ON lc.categoria_id = c.id WHERE lc.libro_id = ? LIMIT 1");
    if (!$stmt) {
        return "Error de consulta";
    }
    $stmt->bind_param("i", $id_libro);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $stmt->close();
        return htmlspecialchars($row['nombre']);
    }
    $stmt->close();
    return "Sin categoría";
}

// Procesar eliminación
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['eliminar_libro'])) {
    $idEliminar = intval($_POST['id']);
    // Borrar relaciones
    $conn->query("DELETE FROM libro_carrera WHERE libro_id = $idEliminar");
    $conn->query("DELETE FROM libro_categorias WHERE libro_id = $idEliminar");
    // Borrar el libro
    $sqlEliminar = "DELETE FROM Libros WHERE id = $idEliminar";
    if ($conn->query($sqlEliminar)) {
        // Usamos JavaScript para recargar y mostrar un mensaje si se está en la misma página
        echo '<script>alert("Libro eliminado correctamente."); window.location.href="libros.php";</script>';
        exit;
    } else {
        $mensajeError = "Error al eliminar el libro: " . $conn->error;
    }
}

// Procesar edición
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['editar_libro'])) {
    $id = intval($_POST['id']);
    $titulo = $conn->real_escape_string($_POST['titulo']);
    $autor = $conn->real_escape_string($_POST['autor']);
    $categoria_id = intval($_POST['categoria_id']); // Nuevo nombre de variable
    $isbn = $conn->real_escape_string($_POST['isbn']);
    $editorial = $conn->real_escape_string($_POST['editorial']);
    $cantidad = intval($_POST['cantidad']);
    $descripcion = isset($_POST['descripcion']) ? $conn->real_escape_string($_POST['descripcion']) : '';

    // Lógica de Imagen
    $imagen_path = null;
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $nombre_tmp = $_FILES['imagen']['tmp_name'];
        $nombre_archivo = uniqid('img_') . '_' . basename($_FILES['imagen']['name']);
        $ruta_destino = 'uploads/' . $nombre_archivo;
        if (!is_dir('uploads')) mkdir('uploads', 0755, true);
        if (move_uploaded_file($nombre_tmp, $ruta_destino)) {
            $imagen_path = $ruta_destino;
        } else {
            $mensajeError = "Error al subir la imagen.";
        }
    }

    // Mantener imagen actual si no se sube una nueva
    if ($imagen_path === null) {
        $resImagen = $conn->query("SELECT imagen FROM Libros WHERE id = $id");
        if ($resImagen && $resImagen->num_rows > 0) {
            $rowImg = $resImagen->fetch_assoc();
            $imagen_path = $rowImg['imagen'];
        }
    }
    
    // Convertir a string para bind_param
    $cantidad_str = strval($cantidad); 
    
    // Actualizar datos del libro (se eliminó la columna 'categoria' de aquí para usar solo la tabla relacional)
    $sqlUpdate = "UPDATE Libros SET titulo=?, autor=?, isbn=?, editorial=?, cantidad=?, descripcion=?, imagen=? WHERE id=?";
    $stmt = $conn->prepare($sqlUpdate);
    // Tipos: string, string, string, string, string(cantidad), string(descripcion), string(imagen), integer(id)
    $stmt->bind_param("sssssssi", $titulo, $autor, $isbn, $editorial, $cantidad_str, $descripcion, $imagen_path, $id);
    
    if ($stmt->execute()) {
        // 1. Actualizar relaciones con CATEGORÍA (Borrar y Reinsertar el ID único)
        $conn->query("DELETE FROM libro_categorias WHERE libro_id = $id");
        if ($categoria_id > 0) {
            $conn->query("INSERT INTO libro_categorias (libro_id, categoria_id) VALUES ($id, $categoria_id)");
        }

        // 2. Actualizar relaciones con CARRERAS (Borrar viejas, insertar nuevas)
        $conn->query("DELETE FROM libro_carrera WHERE libro_id = $id");
        if (isset($_POST['carrera_ids'])) {
            foreach ($_POST['carrera_ids'] as $carrera_id) {
                $cid = intval($carrera_id);
                // Usar prepared statement para inserciones de relación
                $stmt_rel = $conn->prepare("INSERT INTO libro_carrera (libro_id, carrera_id) VALUES (?, ?)");
                $stmt_rel->bind_param("ii", $id, $cid);
                $stmt_rel->execute();
                $stmt_rel->close();
            }
        }
        $mensajeExito = "Libro actualizado correctamente.";
        // Redirigir para limpiar el POST y reflejar los cambios
        header("Location: detalle_libros.php?id=$id");
        exit;
    } else {
        $mensajeError = "Error al actualizar el libro: " . $conn->error;
    }
    $stmt->close();
}

// ------------------------------------------------------------------
// --- LÓGICA DE CARGA DE DATOS PARA LA VISTA Y EL MODAL ---
// ------------------------------------------------------------------

// Comprobar que se ha pasado un ID
if (!isset($_GET['id'])) {
    echo "<p>ID del libro no especificado.</p>"; 
    $conn->close();
    exit;
}

$id = intval($_GET['id']);
$resultado = $conn->query("SELECT * FROM Libros WHERE id = $id");

if ($resultado && $resultado->num_rows > 0) {
    $libro = $resultado->fetch_assoc();
    
    // Obtener ID de la categoría relacionada (para el pre-select del modal)
    $resCategoria = $conn->query("SELECT categoria_id FROM libro_categorias WHERE libro_id = $id LIMIT 1");
    $categoriaRelacionadaID = ($resCategoria && $resCategoria->num_rows > 0) ? $resCategoria->fetch_assoc()['categoria_id'] : 0;
    
    // Obtener el nombre de la categoría para mostrar en la vista
    $nombreCategoriaLibro = obtenerNombreCategoria($conn, $id);

    // Obtener carreras relacionadas (para pre-chequear los checkboxes del modal)
    $resCarreras = $conn->query("SELECT carrera_id FROM libro_carrera WHERE libro_id = $id");
    $carrerasRelacionadas = [];
    while ($rowCarrera = $resCarreras->fetch_assoc()) {
        $carrerasRelacionadas[] = $rowCarrera['carrera_id'];
    }
} else {
    echo "<p>Libro no encontrado.</p>";
    $conn->close();
    exit;
}

// Obtener TODAS las carreras para llenar el formulario de edición
$carreras = [];
$resultCarreras = $conn->query("SELECT id, nombre FROM carreras ORDER BY nombre");
while ($row = $resultCarreras->fetch_assoc()) {
    $carreras[] = $row;
}

// Obtener TODAS las categorías para llenar el formulario de edición
$todasCategorias = [];
$resultCategorias = $conn->query("SELECT id, nombre FROM categorias ORDER BY nombre");
while ($row = $resultCategorias->fetch_assoc()) {
    $todasCategorias[] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Detalle del Libro - <?php echo htmlspecialchars($libro['titulo']); ?></title>
<link rel="stylesheet" href="style.css" />
<style>
/* Estilos del modal y mensajes */
.modal {
    position: fixed;
    top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0,0,0,0.5);
    display: none; justify-content: center; align-items: center; z-index: 9999;
}
.modal-content {
    background: white; padding: 20px 30px; border-radius: 10px; width: 450px; /* Ancho ajustado */
    max-height: 90vh; /* Máxima altura de la ventana */
    overflow-y: auto; /* Scroll si es necesario */
}
/* Estilo para los checkbox de carrera */
.carrera-checkbox-group label {
    display: block;
    margin-bottom: 5px;
}

#mensaje-exito, #mensaje-error {
    position: fixed; top: 20px; right: 20px; padding: 10px 20px; border-radius: 5px; z-index: 10000;
    display: none; font-weight: bold;
}
#mensaje-exito { background: #4CAF50; color: white; }
#mensaje-error { background: #f44336; color: white; }
</style>
</head>
<body>
<div class="container" style="margin-left: 260px;">
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="logo-and-title">
                <img src="logo1.png" alt="Logo ISFT 38" class="header-logo" />
                <h1>Booktrack</h1>
            </div>
        </div>
        <nav class="main-nav">
            <ul>
                <li><a href="libros.php" class="nav-item">Libros</a></li>
                <li><a href="registro.php" class="nav-item">Registro</a></li>
            </ul>
        </nav>
    </aside>

    <main class="main-content">
        <header class="main-header">
            <h2>Usuario</h2>
        </header>

        <section class="book-detail-section">
            <div class="section-header"><h3>Detalles del Libro</h3></div>

            <div class="book-info-layout">
                <div class="book-image-panel">
                    <div class="book-cover-placeholder">
                        <?php if (!empty($libro['imagen'])): ?>
                            <img src="<?php echo htmlspecialchars($libro['imagen']); ?>" style="max-width:250px; max-height:350px;" />
                        <?php else: ?>
                            <p>No hay imagen</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="book-details-panel">
                    <h3 class="book-title-display"><?php echo htmlspecialchars($libro['titulo']); ?></h3>
                    <div class="book-metadata">
                        <div class="metadata-row"><span class="metadata-label">Autor:</span><span class="metadata-value"><?php echo htmlspecialchars($libro['autor']); ?></span></div>
                        
                        <div class="metadata-row"><span class="metadata-label">Categoría:</span><span class="metadata-value"><?php echo $nombreCategoriaLibro; ?></span></div>
                        
                        <div class="metadata-row"><span class="metadata-label">Descripción:</span><span class="metadata-value"><?php echo htmlspecialchars($libro['descripcion'] ?? 'Sin descripción'); ?></span></div>
                    </div>
                    <div class="book-identifiers">
                        <div class="metadata-row"><span class="metadata-label">ISBN:</span><span class="metadata-value"><?php echo htmlspecialchars($libro['isbn']); ?></span></div>
                    </div>
                    <div class="book-additional-info">
                        <div class="metadata-row"><span class="metadata-label">Editorial:</span><span class="metadata-value"><?php echo htmlspecialchars($libro['editorial']); ?></span></div>
                        <div class="metadata-row"><span class="metadata-label">Cantidad Disponible:</span><span class="metadata-value"><?php echo htmlspecialchars($libro['cantidad']); ?></span></div>
                    </div>
                </div>
            </div>

            <div class="controls-bar" style="margin-top:20px;">
                <button class="btn primary-btn" onclick="abrirModal()">✏️ Editar Libro</button>
                <form method="POST" action="" style="display:inline;">
                    <input type="hidden" name="id" value="<?php echo $libro['id']; ?>">
                    <input type="hidden" name="eliminar_libro" value="1" />
                    <button type="submit" class="btn" style="background-color:#f44336; color:white; margin-left:10px;" onclick="return confirm('¿Estás seguro de que quieres eliminar este libro?')">🗑️ Eliminar</button>
                </form>
            </div>
        </section>
    </main>
</div>

<div class="modal" id="editarModal">
    <div class="modal-content">
        <h3>Editar Libro: <?php echo htmlspecialchars($libro['titulo']); ?></h3>
        <form method="POST" action="" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $libro['id']; ?>">
            <input type="hidden" name="editar_libro" value="1" />

            <label style="font-weight:bold;">Título:</label>
            <input type="text" name="titulo" value="<?php echo htmlspecialchars($libro['titulo']); ?>" required style="width:100%; padding:8px;" /><br>
            <br>
            <label style="font-weight:bold;">Autor:</label>
            <input type="text" name="autor" value="<?php echo htmlspecialchars($libro['autor']); ?>" required style="width:100%; padding:8px;" /><br>
            <br>
            <label style="font-weight:bold;">Categoría:</label>
            <select name="categoria_id" required style="width:100%; padding:8px;"> 
                <option value="0">Seleccione una categoría</option>
                <?php foreach ($todasCategorias as $cat): ?>
                    <option value="<?= $cat['id'] ?>" 
                    <?php if ($cat['id'] == $categoriaRelacionadaID) echo 'selected'; ?>>
                        <?= htmlspecialchars($cat['nombre']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <br><br>
            <label style="font-weight:bold;">ISBN:</label>
            <input type="text" name="isbn" value="<?php echo htmlspecialchars($libro['isbn']); ?>" required style="width:100%; padding:8px;" /><br>
            <br>
            <label style="font-weight:bold;">Editorial:</label>
            <input type="text" name="editorial" value="<?php echo htmlspecialchars($libro['editorial']); ?>" required style="width:100%; padding:8px;" /><br>
            <br>
            <label style="font-weight:bold;">Cantidad:</label>
            <input type="number" name="cantidad" value="<?php echo htmlspecialchars($libro['cantidad']); ?>" required style="width:100%; padding:8px;" /><br>
            <br>
            <label style="font-weight:bold;">Descripción:</label><br>
            <textarea name="descripcion" rows="4" style="width:100%; padding:8px; box-sizing:border-box;"><?php echo htmlspecialchars($libro['descripcion'] ?? ''); ?></textarea><br>
            <br>
            <label style="font-weight:bold;">Imagen (opcional):</label>
            <input type="file" name="imagen" accept="image/*" style="width:100%; margin-bottom:15px;" /><br>
            
            <h4 style="margin-top:0;">Carreras: (Marca todas las que apliquen)</h4>
            <div class="carrera-checkbox-group" style="border: 1px solid #ccc; padding: 10px; border-radius: 5px;">
                <?php foreach ($carreras as $carrera): ?>
                    <label>
                        <input type="checkbox" name="carrera_ids[]" value="<?= $carrera['id'] ?>"
                        <?php if (in_array($carrera['id'], $carrerasRelacionadas)) echo 'checked'; ?>>
                        <?= htmlspecialchars($carrera['nombre']); ?>
                    </label>
                <?php endforeach; ?>
            </div>

            <div class="modal-buttons" style="margin-top:20px; display:flex; justify-content:space-between;">
                <button type="submit" class="btn primary-btn" style="background-color:#2196F3; color:white; padding:10px 15px;">💾 Guardar Cambios</button>
                <button type="button" class="btn" onclick="cerrarModal()" style="background-color:#9e9e9e; color:white; padding:10px 15px;">❌ Cancelar</button>
            </div>
        </form>
    </div>
</div>

<div id="mensaje-exito"><?php echo $mensajeExito; ?></div>
<div id="mensaje-error"><?php echo $mensajeError; ?></div>

<script>
function abrirModal() {
    document.getElementById('editarModal').style.display = 'flex';
}
function cerrarModal() {
    document.getElementById('editarModal').style.display = 'none';
}
window.onload = function() {
    const exito = "<?php echo $mensajeExito; ?>";
    const error = "<?php echo $mensajeError; ?>";
    
    // Muestra el mensaje de éxito o error al cargar la página
    if (exito) {
        const div = document.getElementById('mensaje-exito');
        div.style.display = 'block';
        setTimeout(() => div.style.display = 'none', 3000);
    }
    if (error) {
        const div = document.getElementById('mensaje-error');
        div.style.display = 'block';
        setTimeout(() => div.style.display = 'none', 5000);
    }
}
</script>
</body>
</html>