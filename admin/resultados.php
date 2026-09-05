<?php
// admin/resultados.php - Gestión de Resultados CON SUBIDA DE IMÁGENES
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
require_once '../includes/db.php';

$mensaje = '';
$error = '';

// Crear carpeta de uploads si no existe
$upload_dir = '../uploads/resultados/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Función para subir imágenes
function subirImagen($file, $upload_dir) {
    $tipos_permitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $tipos_permitidos)) {
        return false;
    }
    
    if ($file['size'] > 2 * 1024 * 1024) {
        return false;
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $nombre_archivo = uniqid() . '_' . time() . '.' . $extension;
    $ruta_completa = $upload_dir . $nombre_archivo;
    
    if (move_uploaded_file($file['tmp_name'], $ruta_completa)) {
        return 'uploads/resultados/' . $nombre_archivo;
    }
    
    return false;
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $imagen_path = '';
                if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                    $imagen_path = subirImagen($_FILES['imagen'], $upload_dir);
                    if (!$imagen_path) {
                        $error = 'Error al subir la imagen. Asegúrate de que sea JPG, PNG o GIF (máx 2MB).';
                        break;
                    }
                }
                
                if (empty($error)) {
                    $stmt = $pdo->prepare("INSERT INTO resultados (titulo, situacion, trabajo_realizado, solucion, imagen, fecha, activo, destacado) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $_POST['titulo'],
                        $_POST['situacion'],
                        $_POST['trabajo_realizado'],
                        $_POST['solucion'],
                        $imagen_path,
                        $_POST['fecha'],
                        $_POST['activo'] ?? 1,
                        $_POST['destacado'] ?? 0
                    ]);
                    $mensaje = '✅ Caso agregado correctamente';
                }
                break;
                
            case 'edit':
                // Obtener imagen actual
                $stmt = $pdo->prepare("SELECT imagen FROM resultados WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                $resultado_actual = $stmt->fetch();
                $imagen_path = $resultado_actual['imagen'];
                
                // Procesar nueva imagen si se subió
                if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                    if ($imagen_path && file_exists('../' . $imagen_path)) {
                        unlink('../' . $imagen_path);
                    }
                    $imagen_path = subirImagen($_FILES['imagen'], $upload_dir);
                    if (!$imagen_path) {
                        $error = 'Error al subir la imagen.';
                        break;
                    }
                }
                
                // Eliminar imagen si se solicitó
                if (isset($_POST['eliminar_imagen']) && $_POST['eliminar_imagen'] == '1') {
                    if ($imagen_path && file_exists('../' . $imagen_path)) {
                        unlink('../' . $imagen_path);
                    }
                    $imagen_path = '';
                }
                
                if (empty($error)) {
                    $stmt = $pdo->prepare("UPDATE resultados SET titulo = ?, situacion = ?, trabajo_realizado = ?, solucion = ?, imagen = ?, fecha = ?, activo = ?, destacado = ? WHERE id = ?");
                    $stmt->execute([
                        $_POST['titulo'],
                        $_POST['situacion'],
                        $_POST['trabajo_realizado'],
                        $_POST['solucion'],
                        $imagen_path,
                        $_POST['fecha'],
                        $_POST['activo'] ?? 1,
                        $_POST['destacado'] ?? 0,
                        $_POST['id']
                    ]);
                    $mensaje = '✅ Caso actualizado correctamente';
                }
                break;
                
            case 'delete':
                $stmt = $pdo->prepare("SELECT imagen FROM resultados WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                $resultado = $stmt->fetch();
                if ($resultado && $resultado['imagen'] && file_exists('../' . $resultado['imagen'])) {
                    unlink('../' . $resultado['imagen']);
                }
                
                $stmt = $pdo->prepare("DELETE FROM resultados WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                $mensaje = '✅ Caso eliminado correctamente';
                break;
        }
    }
}

// Obtener resultados
$resultados = $pdo->query("SELECT * FROM resultados ORDER BY fecha DESC")->fetchAll();

// Obtener resultado para editar
$modo_edicion = false;
$resultado_editar = null;
if (isset($_GET['editar'])) {
    $stmt = $pdo->prepare("SELECT * FROM resultados WHERE id = ?");
    $stmt->execute([$_GET['editar']]);
    $resultado_editar = $stmt->fetch();
    if ($resultado_editar) {
        $modo_edicion = true;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultados - Administración</title>
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .upload-preview img {
            max-width: 150px;
            max-height: 150px;
            border-radius: 10px;
            border: 3px solid var(--azul-marino);
            object-fit: cover;
        }
        
        .upload-preview {
            display: inline-block;
            position: relative;
            margin-top: 10px;
        }
        
        .upload-preview .remove-img {
            position: absolute;
            top: -10px;
            right: -10px;
            background: var(--rojo-intenso);
            color: var(--blanco);
            border: none;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            cursor: pointer;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .upload-preview .remove-img:hover {
            transform: scale(1.15);
        }
        
        .form-group.upload-area {
            border: 2px dashed #ddd;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            transition: all 0.3s;
            cursor: pointer;
            background: #fafafa;
            position: relative;
        }
        
        .form-group.upload-area:hover {
            border-color: var(--rojo-intenso);
            background: #fff5f5;
        }
        
        .form-group.upload-area input[type="file"] {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }
        
        .upload-icon {
            font-size: 48px;
            color: #ccc;
            margin-bottom: 10px;
        }
        
        .upload-text {
            color: #999;
            font-size: 14px;
        }
        
        .upload-text strong {
            color: var(--azul-marino);
        }
        
        .upload-formats {
            font-size: 12px;
            color: #bbb;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <aside class="admin-sidebar">
            <div class="admin-logo">
                <img src="../img/logo-defensa-obrera.png" alt="Defensa Obrera">
            </div>
            <nav class="admin-nav">
                <a href="index.php"><i class="fas fa-dashboard"></i> Dashboard</a>
                <a href="servicios.php"><i class="fas fa-gavel"></i> Servicios</a>
                <a href="equipo.php"><i class="fas fa-users"></i> Equipo</a>
                <a href="resultados.php" class="active"><i class="fas fa-chart-bar"></i> Resultados</a>
                <a href="recursos.php"><i class="fas fa-book"></i> Recursos</a>
                <a href="mensajes.php"><i class="fas fa-envelope"></i> Mensajes</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
            </nav>
        </aside>
        
        <main class="admin-main">
            <header class="admin-header">
                <h1>Gestión de Resultados</h1>
            </header>
            
            <div class="admin-content">
                <?php if ($mensaje): ?>
                    <div class="alert alert-success"><?php echo $mensaje; ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <!-- Formulario -->
                <div class="admin-card">
                    <h2><?php echo $modo_edicion ? '✏️ Editar Caso' : '➕ Agregar Caso/Resultado'; ?></h2>
                    <form method="POST" class="admin-form" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="<?php echo $modo_edicion ? 'edit' : 'add'; ?>">
                        <?php if ($modo_edicion): ?>
                            <input type="hidden" name="id" value="<?php echo $resultado_editar['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label>Título del caso *</label>
                            <input type="text" name="titulo" required value="<?php echo $modo_edicion ? htmlspecialchars($resultado_editar['titulo']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label>Situación del trabajador *</label>
                            <textarea name="situacion" required rows="3"><?php echo $modo_edicion ? htmlspecialchars($resultado_editar['situacion']) : ''; ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>Trabajo realizado *</label>
                            <textarea name="trabajo_realizado" required rows="3"><?php echo $modo_edicion ? htmlspecialchars($resultado_editar['trabajo_realizado']) : ''; ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>Solución obtenida *</label>
                            <textarea name="solucion" required rows="3"><?php echo $modo_edicion ? htmlspecialchars($resultado_editar['solucion']) : ''; ?></textarea>
                        </div>
                        
                        <!-- Área de subida de imagen -->
                        <div class="form-group upload-area">
                            <input type="file" name="imagen" accept="image/*">
                            <div class="upload-icon"><i class="fas fa-image"></i></div>
                            <div class="upload-text"><strong>Haz clic o arrastra</strong> una imagen para subir</div>
                            <div class="upload-formats">Formatos: JPG, PNG, GIF, WebP | Máx: 2MB</div>
                            
                            <?php if ($modo_edicion && $resultado_editar['imagen']): ?>
                                <div class="upload-preview" id="previewExistente">
                                    <img src="../<?php echo htmlspecialchars($resultado_editar['imagen']); ?>" alt="Imagen actual">
                                    <button type="button" class="remove-img" onclick="eliminarImagenExistente()">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <input type="hidden" name="eliminar_imagen" id="eliminar_imagen" value="0">
                            <?php endif; ?>
                            
                            <div id="previewNueva" style="display:none;" class="upload-preview">
                                <img id="previewImg" src="" alt="Vista previa">
                                <button type="button" class="remove-img" onclick="removerNuevaPreview()">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Fecha</label>
                            <input type="date" name="fecha" value="<?php echo $modo_edicion ? $resultado_editar['fecha'] : date('Y-m-d'); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="destacado" value="1" <?php echo ($modo_edicion && $resultado_editar['destacado']) ? 'checked' : ''; ?>> 
                                ⭐ Destacado
                            </label>
                        </div>
                        
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="activo" value="1" <?php echo ($modo_edicion && $resultado_editar['activo']) || !$modo_edicion ? 'checked' : ''; ?>> 
                                Activo (visible en la página)
                            </label>
                        </div>
                        
                        <button type="submit" class="btn-primary">
                            <?php echo $modo_edicion ? 'Actualizar Caso' : 'Agregar Caso'; ?>
                        </button>
                        
                        <?php if ($modo_edicion): ?>
                            <a href="resultados.php" class="btn-secondary" style="display:inline-block;padding:12px 30px;background:var(--gris-claro);color:var(--azul-marino);border-radius:5px;text-decoration:none;font-weight:600;margin-left:10px;">
                                Cancelar
                            </a>
                        <?php endif; ?>
                    </form>
                </div>
                
                <!-- Lista de Resultados -->
                <div class="admin-card">
                    <h2>Casos Registrados (<?php echo count($resultados); ?>)</h2>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Imagen</th>
                                <th>Título</th>
                                <th>Fecha</th>
                                <th>Destacado</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($resultados as $resultado): ?>
                            <tr>
                                <td><?php echo $resultado['id']; ?></td>
                                <td>
                                    <?php if (!empty($resultado['imagen'])): ?>
                                        <img src="../<?php echo htmlspecialchars($resultado['imagen']); ?>" style="width:50px;height:50px;border-radius:5px;object-fit:cover;">
                                    <?php else: ?>
                                        <i class="fas fa-image" style="font-size:30px;color:#ccc;"></i>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($resultado['titulo']); ?></td>
                                <td><?php echo $resultado['fecha'] ? date('d/m/Y', strtotime($resultado['fecha'])) : '-'; ?></td>
                                <td><?php echo $resultado['destacado'] ? '⭐ Sí' : '-'; ?></td>
                                <td><?php echo $resultado['activo'] ? '✅ Activo' : '❌ Inactivo'; ?></td>
                                <td class="actions">
                                    <a href="?editar=<?php echo $resultado['id']; ?>" class="btn-edit">Editar</a>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $resultado['id']; ?>">
                                        <button type="submit" class="btn-delete" onclick="return confirm('¿Eliminar este caso?')">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        // Preview de nueva imagen
        const uploadArea = document.querySelector('.upload-area');
        const fileInput = document.querySelector('input[name="imagen"]');
        const previewNueva = document.getElementById('previewNueva');
        const previewImg = document.getElementById('previewImg');
        
        if (fileInput) {
            fileInput.addEventListener('change', function() {
                if (this.files.length > 0) {
                    const file = this.files[0];
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImg.src = e.target.result;
                        previewNueva.style.display = 'inline-block';
                        if (document.getElementById('previewExistente')) {
                            document.getElementById('previewExistente').style.display = 'none';
                        }
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
        
        function removerNuevaPreview() {
            previewNueva.style.display = 'none';
            previewImg.src = '';
            fileInput.value = '';
            if (document.getElementById('previewExistente')) {
                document.getElementById('previewExistente').style.display = 'inline-block';
            }
        }
        
        function eliminarImagenExistente() {
            if (confirm('¿Eliminar la imagen actual?')) {
                document.getElementById('previewExistente').style.display = 'none';
                document.getElementById('eliminar_imagen').value = '1';
            }
        }
    </script>
</body>
</html>