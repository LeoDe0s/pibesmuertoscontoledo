<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "biblioteca_isft38";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("Error de conexión: " . $conn->connect_error);

// Si llegan datos via fetch (JSON POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
    $data = json_decode(file_get_contents("php://input"), true);
    if ($data) {
        foreach ($data as $row) {
            $id = $row[0];
            $miembro = $conn->real_escape_string($row[1]);
            $titulo = $conn->real_escape_string($row[2]);
            $fechaP = $row[3];
            $fechaD = $row[4];
            $estado = $row[5];
            $vencimientos = $conn->real_escape_string($row[6]);

            // Actualiza si existe el ID
            $sql = "UPDATE prestamos SET 
                        miembro='$miembro', 
                        titulo_libro='$titulo', 
                        fecha_prestamo='$fechaP', 
                        fecha_devolucion='$fechaD', 
                        estado='$estado', 
                        vencimientos='$vencimientos' 
                    WHERE id=$id";

            if (!$conn->query($sql)) {
                echo "Error al actualizar ID $id: " . $conn->error . "\n";
            } else {
                echo "Fila $id actualizada correctamente\n";
            }
        }
    } else {
        echo "No se recibieron datos";
    }
    exit;
}

$sql = "SELECT * FROM prestamos";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booktrack - Registro</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="logo-and-title">
                    <img src="img/logo1.png" alt="Logo ISFT 38" class="header-logo">
                    <h1>Booktrack</h1>
                </div>
            </div>
            <nav class="main-nav">
                <ul>
                    <li><a href="libros.php" class="nav-item active">Libros</a></li>
                    <li><a href="registro.php" class="nav-item">Registro</a></li>
                </ul>
            </nav>
            <div class="sidebar-image">
                <img src="iconlibro.png" alt="Icono de libro y pluma">
            </div>
        </aside>

        <main class="main-content">
            <header class="main-header">
                <h2>Usuario</h2>
            </header>

            <section class="record-section">
                <div class="section-header">
                    <h3>Registro</h3>
                    <div class="search-input-wrapper">
                        <input type="text" placeholder="">
                        <img src="img/Lupa_2.png" alt="Icono de búsqueda" class="search-icon-img">
                    </div>
                </div>

                <div class="colores">
                    <span class="titulo">
                        <h1>Guía de colores</h1>
                        <h5>Devuelto  Prestado  Vencido</h5>
                    </span>
                    <div class="color-icons">
                        <span class="icono verde" title="Devuelto"></span>
                        <span class="icono amarillo" title="Prestado"></span>
                        <span class="icono rojo" title="Vencido"></span>
                    </div>
                    <div class="controls-bar">
                        <button class="btn primary-btn" id="edit-btn">Editar</button>
                    </div>
                </div>

                <div class="table-container">
                    <div class="table-title">Registro de Préstamos y Devoluciones</div>
                    <table class="record-table" id="record-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Miembro</th>
                                <th>Título del libro</th>
                                <th>Fecha Préstamo</th>
                                <th>Fecha Devolución</th>
                                <th>Estado</th>
                                <th>Vencimientos</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                            <tr data-id="<?php echo $row['id']; ?>">
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['miembro']); ?></td>
                                <td><?php echo htmlspecialchars($row['titulo_libro']); ?></td>
                                <td><?php echo $row['fecha_prestamo']; ?></td>
                                <td><?php echo $row['fecha_devolucion']; ?></td>
                                <td><?php echo $row['estado']; ?></td>
                                <td><?php echo htmlspecialchars($row['vencimientos']); ?></td>
                            </tr>
                            <?php endwhile; ?>

                            
                            <tr>
                                <td>...</td>
                                <td>...</td>
                                <td>...</td>
                                <td>...</td>
                                <td>...</td>
                                <td>...</td>
                                <td>...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>
    <script src="js/editar_tablas.js"></script>
</body>
</html>
