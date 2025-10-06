<?php
$host = "localhost";
$user = "root";
$password = "";
$dbname = "biblioteca_isft38";

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("<h2>Error de conexión: " . $conn->connect_error . "</h2>");
}
$conn->set_charset("utf8mb4");

// Función para insertar libro
function insertarLibro($titulo, $autor, $categorias, $editorial, $cantidad, $isbn, $imagen_path) {
    global $conn;

    $sql = "INSERT INTO libros (titulo, autor, categorias, editorial, cantidad, isbn, imagen) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Error en la preparación de la consulta: " . $conn->error);
    }

    $stmt->bind_param("ssssiss", $titulo, $autor, $categorias, $editorial, $cantidad, $isbn, $imagen_path);

    try {
        if ($stmt->execute()) {
            return $conn->insert_id;
        } else {
            return false;
        }
    } catch (mysqli_sql_exception $e) {
        // Detectar duplicado por ISBN
        if ($e->getCode() === 1062) {
            return "duplicado";
        } else {
            return false;
        }
    }
}


$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit_libro"])) {
    $titulo = $_POST["titulo_libro"];
    $autor = $_POST["autor"];
    
    $categorias_array = isset($_POST["categorias"]) ? array_map('intval', [$_POST["categorias"]]) : [];
    $categorias_str = implode(',', $categorias_array);
    $editorial = $_POST["editorial"];
    $cantidad = intval($_POST["cantidad_disponible"]);
    $isbn = $_POST["isbn"];

    $imagen_path = "";
    if (isset($_FILES["imagen"]) && $_FILES["imagen"]["error"] === UPLOAD_ERR_OK) {
        $nombre_tmp = $_FILES["imagen"]["tmp_name"];
        $nombre_archivo = uniqid("img_") . "_" . basename($_FILES["imagen"]["name"]);
        $ruta_destino = "uploads/" . $nombre_archivo;
        if (move_uploaded_file($nombre_tmp, $ruta_destino)) {
            $imagen_path = $ruta_destino;
        }
    }

    $libro_id = insertarLibro($titulo, $autor, $categorias_str, $editorial, $cantidad, $isbn, $imagen_path);

    $status_script = "";

    if (is_numeric($libro_id)) {
        foreach ($categorias_array as $cat_id) {
            if ($cat_id > 0) {
                $sql_rel = "INSERT INTO libro_categorias (libro_id, categoria_id) VALUES (?, ?)";
                $stmt_rel = $conn->prepare($sql_rel);
                $stmt_rel->bind_param("ii", $libro_id, $cat_id);
                $stmt_rel->execute();
            }
        }

        $carrera_id = isset($_POST['carrera']) ? intval($_POST['carrera']) : 0;
        if ($carrera_id != 0) {
            $sql_rel_carrera = "INSERT INTO libro_carrera (libro_id, carrera_id) VALUES (?, ?)";
            $stmt_carrera = $conn->prepare($sql_rel_carrera);
            $stmt_carrera->bind_param("ii", $libro_id, $carrera_id);
            $stmt_carrera->execute();
        }

        $status_script = '<script>
            document.getElementById("popupFormulario").style.display = "none";
            alert("📚 Libro cargado correctamente.");
            window.location.href = "libros.php"; 
        </script>';
    } elseif ($libro_id === "duplicado") {
        $status_script = '<script>
            alert("❗ El ISBN ya existe en la base de datos. No se puede cargar el libro duplicado.");
        </script>';
    } else {
        $status_script = '<script>alert("❌ Error al cargar el libro. Por favor, revise los datos.");</script>';
    }
}

$categorias_id_filtro = isset($_GET['categorias_id']) && is_numeric($_GET['categorias_id']) ? intval($_GET['categorias_id']) : 0;
$carrera_id_filtro = isset($_GET['carrera_id']) && is_numeric($_GET['carrera_id']) ? intval($_GET['carrera_id']) : 0;


$sqlLibros = "SELECT DISTINCT l.id, l.titulo AS Titulo, l.imagen
             FROM libros l
             LEFT JOIN libro_categorias lc ON l.id = lc.libro_id
             LEFT JOIN categorias c ON lc.categoria_id = c.id
             LEFT JOIN libro_carrera lcar ON l.id = lcar.libro_id
             LEFT JOIN carreras car ON lcar.carrera_id = car.id
             WHERE 1=1";

if ($categorias_id_filtro != 0) {
    $sqlLibros .= " AND c.id = " . $categorias_id_filtro;
}
if ($carrera_id_filtro != 0) {
    $sqlLibros .= " AND car.id = " . $carrera_id_filtro;
}

$buscar = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';

if ($buscar !== '') {
    $sqlLibros .= " AND l.titulo LIKE '%" . $conn->real_escape_string($buscar) . "%'";
}

$resultLibros = $conn->query($sqlLibros);
?>


<!DOCTYPE html>
<html lang="es">

  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Gestión de Biblioteca Escolar</title>
    <link rel="stylesheet" href="css/style.css" />
    <link rel="stylesheet" href="css/modal_menu.css"/> 
  </head>

  <body>
    <div class="container" style="margin-left: 260px;">
      <aside class="sidebar">
        <div class="sidebar-header">
          <div class="logo-and-title">
            <img src="img/logo1.png" alt="Logo ISFT 38" class="header-logo" />
            <h1>Booktrack</h1>
          </div>
        </div>
        <nav class="main-nav">
          <ul>
            <li><a href="libros.php" class="nav-item active">Libros</a></li>
            <li><a href="registro.php" class="nav-item">Registro</a></li>
          </ul>
        </nav>
      </aside>

      <main class="main-content">
        <header class="main-header">
          <h2>Usuario</h2>
        </header>
        <section class="books-section">
          <div class="section-header">
            <h3>Libros</h3>
            <div style="display: flex; height: 35px; align-items: center;">
              <div class="controls-bar" style="margin-right: 10px;">
                <button class="btn" onclick="abrirPopup()">Cargar Libro</button>
              </div>
              <div class="search-input-wrapper">
                <form method="GET" action="" style="display:inline;">
                  <input type="text" name="buscar" placeholder="Búsqueda" value="<?php echo isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : ''; ?>" />
                  <img src="img/Lupa_2.png" alt="Icono de búsqueda" class="search-icon-img" />
                </form>
              </div>
            </div>
          </div>
          <div class="books-layout" style="display:flex;">
            <div class="filters-panel" style="width:200px; margin-right:20px; background:#f0f0f4; padding:10px; border-radius:8px;">
              <h4 style="margin-top:0;">Filtros</h4>
              <div style="margin-bottom:20px;">
                <strong style="cursor:pointer;" onclick="toggleCategorias()">Categorías</strong>
                <div id="categoriasLista" style="margin-top:10px;">
                  <ul style="list-style:none; padding-left:0;">
                    <li style="margin-bottom:8px;">
                      <a href="?categorias_id=0&carrera_id=0" style="text-decoration:none;">Todas</a>
                    </li>
                    <?php
                      $categorias_result = $conn->query("SELECT id, nombre FROM categorias");
                      if ($categorias_result) {
                        while ($cat = $categorias_result->fetch_assoc()) {
                          $isSelected = ($categorias_id_filtro == $cat['id']);
                          $style = "display:block; padding:6px 10px; border-radius:4px; text-decoration:none; ";
                          if ($isSelected) {
                            $style .= "background-color:#ddd; font-weight:bold;";
                          }
                          echo '<li style="margin-bottom:8px;">';
                          echo '<a href="?categorias_id=' . $cat['id'] . '&carrera_id=' . $carrera_id_filtro . '" style="' . $style . '">' . htmlspecialchars($cat['nombre']) . '</a>';
                          echo '</li>';
                        }
                      }
                    ?>
                  </ul>
                </div>
              </div>
              <div style="margin-bottom: 20px;">
                <strong style="cursor: pointer;" onclick="toggleCarreras()">Carreras</strong>
                <div id="carrerasLista" style="margin-top: 10px;">
                  <ul style="list-style: none; padding-left: 0;">
                    <li style="margin-bottom: 8px;">
                      <a href="?categorias_id=0&carrera_id=0" style="text-decoration: none;">Todas</a>
                    </li>
                    <?php
                      $carreras_result = $conn->query("SELECT id, nombre FROM carreras");
                      if ($carreras_result) {
                        while ($car = $carreras_result->fetch_assoc()) {
                          $isSelected = ($carrera_id_filtro == $car['id']);
                          $style = "display:block; padding:6px 10px; border-radius:4px; text-decoration:none; ";
                          if ($isSelected) {
                            $style .= "background-color:#ddd; font-weight:bold;";
                          }
                          echo '<li style="margin-bottom:8px;">';
                          echo '<a href="?categorias_id=' . $categorias_id_filtro . '&carrera_id=' . $car['id'] . '" style="' . $style . '">' . htmlspecialchars($car['nombre']) . '</a>';
                          echo '</li>';
                        }
                      }
                    ?>
                  </ul>
                </div>
              </div>
            </div>
            <div class="books-grid-panel" style="flex:1; display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 15px;">
              <?php
                if ($resultLibros && $resultLibros->num_rows > 0) {
                  while ($row = $resultLibros->fetch_assoc()) {
                    $idLibro = $row['id'];
                    $titulo = htmlspecialchars($row['Titulo']);
                    $imagen = (!empty($row['imagen']) && file_exists($row['imagen'])) ? $row['imagen'] : 'uploads/default.png';
                    echo '<a href="detalle_libros.php?id=' . urlencode($idLibro) . '" class="book-card-link">';
                    echo '<div class="book-card">';
                    echo '<div style="display:inline-block;">';
                    echo '<img src="' . htmlspecialchars($imagen) . '" alt="Portada del libro" />';
                    echo '<div class="title">' . $titulo . '</div>';
                    echo '</div>';
                    echo '</div>';
                    echo '</a>';
                  }
                } else {
                  echo "<p>No se encontraron libros.</p>";
                }
              ?>
            </div>
          </div>
        </section>
      </main>
    </div>
    <div class="popup_modal" id="popupFormulario">
      <div class="popup_content" id="popupContent">
        <span onclick="cerrarPopup()" class="popup_close">✖</span>
        <h2 style="text-align:center; margin-bottom:20px;">Nuevo Libro</h2>
        <form method="post" enctype="multipart/form-data" style="display:flex; flex-direction:column; gap:15px;">
          <label for="titulo_libro" style="font-weight:bold;">Título:</label>
          <input class="form_input" type="text" name="titulo_libro" required />
          
          <label for="autor" style="font-weight:bold;">Autor:</label>
          <input class="form_input" type="text" name="autor" required />
          
          <label for="categorias" style="font-weight:bold;">Categorías:</label>
          <select class="form_category" name="categorias" required>
            <option value="" disabled selected>Seleccione una categoría</option> 
            <?php
              $categorias_result = $conn->query("SELECT id, nombre FROM categorias");
              if ($categorias_result) {
                while ($cat = $categorias_result->fetch_assoc()) {
                  echo '<option value="' . $cat['id'] . '">' . htmlspecialchars($cat['nombre']) . '</option>';
                }
              }
            ?>
          </select>
          <label for="carrera" style="font-weight:bold;">Carrera:</label>
          <select class="form_select" name="carrera" required>
            <option value="" disabled selected>Seleccione una carrera</option>
            <?php
              $carreras_result = $conn->query("SELECT id, nombre FROM carreras");
              if ($carreras_result) {
                while ($car = $carreras_result->fetch_assoc()) {
                  echo '<option value="' . $car['id'] . '">' . htmlspecialchars($car['nombre']) . '</option>';
                }
              }
            ?>
          </select>
          
          <label for="editorial" style="font-weight:bold;">Editorial:</label>
          <input type="text" name="editorial" required style="padding:8px; border:1px solid #ccc; border-radius:4px;" />
          <label for="cantidad_disponible" style="font-weight:bold;">Cantidad:</label>
          <input type="number" name="cantidad_disponible" min="1" required style="padding:8px; border:1px solid #ccc; border-radius:4px;" />
          <label for="isbn" style="font-weight:bold;">ISBN:</label>
          <input type="text" name="isbn" required style="padding:8px; border:1px solid #ccc; border-radius:4px;" />
          <label for="imagen" style="font-weight:bold;">Imagen:</label>
          <input type="file" name="imagen" accept="image/*" style="border: none;" />
          <button type="submit" class="btn" name="submit_libro" style="margin-top:20px; padding:10px; background-color:#4CAF50; color:#fff; border:none; border-radius:4px; cursor:pointer;">Guardar Libro</button>
        </form>
      </div>
    </div>
    
    <?php echo isset($status_script) ? $status_script : ''; ?> 
    
      <script src="./js/modal_menu.js"></script>
      <script src="./js/app.js"></script>
      
  </body>
</html>