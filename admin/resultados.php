<?php
// admin/resultados.php - Gestión de Resultados Y TESTIMONIOS
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
require_once '../includes/db.php';

$mensaje = '';
$error = '';

// ============================================
// RUTAS PARA SUBIDA DE IMÁGENES
// ============================================

$upload_dir = __DIR__ . '/../uploads/resultados/';
$upload_url = 'uploads/resultados/';

if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

function subirImagen($file, $upload_dir, $upload_url) {
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
        return $upload_url . $nombre_archivo;
    }
    return false;
}

// ============================================
// PROCESAR FORMULARIO - RESULTADOS
// ============================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            // ============================================
            // AGREGAR RESULTADO
            // ============================================
            case 'add_resultado':
                $imagen_path = '';
                if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                    $imagen_path = subirImagen($_FILES['imagen'], $upload_dir, $upload_url);
                    if (!$imagen_path) {
                        $error = 'Error al subir la imagen.';
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
                
            // ============================================
            // EDITAR RESULTADO
            // ============================================
            case 'edit_resultado':
                $stmt = $pdo->prepare("SELECT imagen FROM resultados WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                $resultado_actual = $stmt->fetch();
                $imagen_path = $resultado_actual['imagen'] ?? '';
                
                if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                    if ($imagen_path && file_exists(__DIR__ . '/../' . $imagen_path)) {
                        unlink(__DIR__ . '/../' . $imagen_path);
                    }
                    $imagen_path = subirImagen($_FILES['imagen'], $upload_dir, $upload_url);
                    if (!$imagen_path) {
                        $error = 'Error al subir la imagen.';
                        break;
                    }
                }
                
                if (isset($_POST['eliminar_imagen']) && $_POST['eliminar_imagen'] == '1') {
                    if ($imagen_path && file_exists(__DIR__ . '/../' . $imagen_path)) {
                        unlink(__DIR__ . '/../' . $imagen_path);
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
                
            // ============================================
            // ELIMINAR RESULTADO
            // ============================================
            case 'delete_resultado':
                $stmt = $pdo->prepare("SELECT imagen FROM resultados WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                $resultado = $stmt->fetch();
                if ($resultado && $resultado['imagen'] && file_exists(__DIR__ . '/../' . $resultado['imagen'])) {
                    unlink(__DIR__ . '/../' . $resultado['imagen']);
                }
                $stmt = $pdo->prepare("DELETE FROM resultados WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                $mensaje = '✅ Caso eliminado correctamente';
                break;
                
            // ============================================
            // AGREGAR TESTIMONIO
            // ============================================
            case 'add_testimonio':
                $foto_path = '';
                if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                    $foto_path = subirImagen($_FILES['foto'], $upload_dir, $upload_url);
                    if (!$foto_path) {
                        $error = 'Error al subir la foto.';
                        break;
                    }
                }
                if (empty($error)) {
                    $stmt = $pdo->prepare("INSERT INTO testimonios (nombre, testimonio, foto, orden, activo) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $_POST['nombre'],
                        $_POST['testimonio'],
                        $foto_path,
                        $_POST['orden'] ?? 0,
                        $_POST['activo'] ?? 1
                    ]);
                    $mensaje = '✅ Testimonio agregado correctamente';
                }
                break;
                
            // ============================================
            // EDITAR TESTIMONIO
            // ============================================
            case 'edit_testimonio':
                $stmt = $pdo->prepare("SELECT foto FROM testimonios WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                $testimonio_actual = $stmt->fetch();
                $foto_path = $testimonio_actual['foto'] ?? '';
                
                if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                    if ($foto_path && file_exists(__DIR__ . '/../' . $foto_path)) {
                        unlink(__DIR__ . '/../' . $foto_path);
                    }
                    $foto_path = subirImagen($_FILES['foto'], $upload_dir, $upload_url);
                    if (!$foto_path) {
                        $error = 'Error al subir la foto.';
                        break;
                    }
                }
                
                if (isset($_POST['eliminar_foto']) && $_POST['eliminar_foto'] == '1') {
                    if ($foto_path && file_exists(__DIR__ . '/../' . $foto_path)) {
                        unlink(__DIR__ . '/../' . $foto_path);
                    }
                    $foto_path = '';
                }
                
                if (empty($error)) {
                    $stmt = $pdo->prepare("UPDATE testimonios SET nombre = ?, testimonio = ?, foto = ?, orden = ?, activo = ? WHERE id = ?");
                    $stmt->execute([
                        $_POST['nombre'],
                        $_POST['testimonio'],
                        $foto_path,
                        $_POST['orden'] ?? 0,
                        $_POST['activo'] ?? 1,
                        $_POST['id']
                    ]);
                    $mensaje = '✅ Testimonio actualizado correctamente';
                }
                break;
                
            // ============================================
            // ELIMINAR TESTIMONIO
            // ============================================
            case 'delete_testimonio':
                $stmt = $pdo->prepare("SELECT foto FROM testimonios WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                $testimonio = $stmt->fetch();
                if ($testimonio && $testimonio['foto'] && file_exists(__DIR__ . '/../' . $testimonio['foto'])) {
                    unlink(__DIR__ . '/../' . $testimonio['foto']);
                }
                $stmt = $pdo->prepare("DELETE FROM testimonios WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                $mensaje = '✅ Testimonio eliminado correctamente';
                break;
        }
    }
}

// ============================================
// OBTENER DATOS
// ============================================

$resultados = $pdo->query("SELECT * FROM resultados ORDER BY fecha DESC")->fetchAll();
$testimonios = $pdo->query("SELECT * FROM testimonios ORDER BY orden")->fetchAll();

// Obtener resultado para editar
$modo_edicion_resultado = false;
$resultado_editar = null;
if (isset($_GET['editar_resultado'])) {
    $stmt = $pdo->prepare("SELECT * FROM resultados WHERE id = ?");
    $stmt->execute([$_GET['editar_resultado']]);
    $resultado_editar = $stmt->fetch();
    if ($resultado_editar) {
        $modo_edicion_resultado = true;
    }
}

// Obtener testimonio para editar
$modo_edicion_testimonio = false;
$testimonio_editar = null;
if (isset($_GET['editar_testimonio'])) {
    $stmt = $pdo->prepare("SELECT * FROM testimonios WHERE id = ?");
    $stmt->execute([$_GET['editar_testimonio']]);
    $testimonio_editar = $stmt->fetch();
    if ($testimonio_editar) {
        $modo_edicion_testimonio = true;
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
            max-width: 120px;
            max-height: 120px;
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
            min-height: 120px;
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
            font-size: 32px;
            color: #ccc;
            margin-bottom: 5px;
        }
        .upload-text {
            color: #999;
            font-size: 13px;
        }
        .upload-text strong {
            color: var(--azul-marino);
        }
        .upload-formats {
            font-size: 11px;
            color: #bbb;
            margin-top: 3px;
        }
        .resultado-img-mini {
            width: 50px;
            height: 50px;
            border-radius: 5px;
            object-fit: cover;
            border: 2px solid var(--azul-marino);
        }
        .testimonio-foto-mini {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--azul-marino);
        }
        .badge-testimonio {
            display: inline-block;
            background: #17a2b8;
            color: #fff;
            padding: 2px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }
        .badge-caso {
            display: inline-block;
            background: #28a745;
            color: #fff;
            padding: 2px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }
        .tab-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }
        .tab-buttons .tab-btn {
            padding: 10px 25px;
            border: none;
            background: var(--gris-claro);
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }
        .tab-buttons .tab-btn.active {
            background: var(--azul-marino);
            color: var(--blanco);
        }
        .tab-buttons .tab-btn:hover {
            background: var(--azul-marino);
            color: var(--blanco);
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
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
                <h1>Gestión de Resultados y Testimonios</h1>
            </header>
            <div class="admin-content">
                <?php if ($mensaje): ?>
                    <div class="alert alert-success"><?php echo $mensaje; ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <!-- ============================================
                PESTAÑAS
                ============================================ -->
                <div class="tab-buttons">
                    <button class="tab-btn active" onclick="mostrarTab('casos')">📊 Casos</button>
                    <button class="tab-btn" onclick="mostrarTab('testimonios')">💬 Testimonios</button>
                </div>
                
                <!-- ============================================
                TAB: CASOS
                ============================================ -->
                <div id="tab-casos" class="tab-content active">
                    <!-- Formulario Agregar/Editar Caso -->
                    <div class="admin-card">
                        <h2><?php echo $modo_edicion_resultado ? '✏️ Editar Caso' : '➕ Agregar Caso/Resultado'; ?></h2>
                        <form method="POST" class="admin-form" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="<?php echo $modo_edicion_resultado ? 'edit_resultado' : 'add_resultado'; ?>">
                            <?php if ($modo_edicion_resultado): ?>
                                <input type="hidden" name="id" value="<?php echo $resultado_editar['id']; ?>">
                            <?php endif; ?>
                            
                            <div class="form-group">
                                <label>Título del caso *</label>
                                <input type="text" name="titulo" required value="<?php echo $modo_edicion_resultado ? htmlspecialchars($resultado_editar['titulo']) : ''; ?>">
                            </div>
                            <div class="form-group">
                                <label>Situación del trabajador *</label>
                                <textarea name="situacion" required rows="3"><?php echo $modo_edicion_resultado ? htmlspecialchars($resultado_editar['situacion']) : ''; ?></textarea>
                            </div>
                            <div class="form-group">
                                <label>Trabajo realizado *</label>
                                <textarea name="trabajo_realizado" required rows="3"><?php echo $modo_edicion_resultado ? htmlspecialchars($resultado_editar['trabajo_realizado']) : ''; ?></textarea>
                            </div>
                            <div class="form-group">
                                <label>Solución obtenida *</label>
                                <textarea name="solucion" required rows="3"><?php echo $modo_edicion_resultado ? htmlspecialchars($resultado_editar['solucion']) : ''; ?></textarea>
                            </div>
                            
                            <div class="form-group upload-area">
                                <input type="file" name="imagen" accept="image/*">
                                <div class="upload-icon"><i class="fas fa-image"></i></div>
                                <div class="upload-text"><strong>Haz clic o arrastra</strong> una imagen para subir</div>
                                <div class="upload-formats">Formatos: JPG, PNG, GIF, WebP | Máx: 2MB</div>
                                
                                <?php if ($modo_edicion_resultado && !empty($resultado_editar['imagen'])): ?>
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
                                <input type="date" name="fecha" value="<?php echo $modo_edicion_resultado ? $resultado_editar['fecha'] : date('Y-m-d'); ?>">
                            </div>
                            <div class="form-group">
                                <label>
                                    <input type="checkbox" name="destacado" value="1" <?php echo ($modo_edicion_resultado && $resultado_editar['destacado']) ? 'checked' : ''; ?>> 
                                    ⭐ Destacado
                                </label>
                            </div>
                            <div class="form-group">
                                <label>
                                    <input type="checkbox" name="activo" value="1" <?php echo ($modo_edicion_resultado && $resultado_editar['activo']) || !$modo_edicion_resultado ? 'checked' : ''; ?>> 
                                    Activo
                                </label>
                            </div>
                            <button type="submit" class="btn-primary">
                                <?php echo $modo_edicion_resultado ? 'Actualizar Caso' : 'Agregar Caso'; ?>
                            </button>
                            <?php if ($modo_edicion_resultado): ?>
                                <a href="resultados.php" class="btn-secondary" style="display:inline-block;padding:12px 30px;background:var(--gris-claro);color:var(--azul-marino);border-radius:5px;text-decoration:none;font-weight:600;margin-left:10px;">Cancelar</a>
                            <?php endif; ?>
                        </form>
                    </div>
                    
                    <!-- Lista de Resultados -->
                    <div class="admin-card">
                        <h2>Casos Registrados (<?php echo count($resultados); ?>)</h2>
                        <table class="admin-table">
                            <thead>
                                <tr><th>#</th><th>Imagen</th><th>Título</th><th>Fecha</th><th>Destacado</th><th>Estado</th><th>Acciones</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach($resultados as $resultado): ?>
                                <tr>
                                    <td><?php echo $resultado['id']; ?></td>
                                    <td>
                                        <?php if (!empty($resultado['imagen'])): ?>
                                            <img src="../<?php echo htmlspecialchars($resultado['imagen']); ?>" class="resultado-img-mini">
                                        <?php else: ?>
                                            <i class="fas fa-image" style="font-size:30px;color:#ccc;"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($resultado['titulo']); ?></td>
                                    <td><?php echo $resultado['fecha'] ? date('d/m/Y', strtotime($resultado['fecha'])) : '-'; ?></td>
                                    <td><?php echo $resultado['destacado'] ? '⭐ Sí' : '-'; ?></td>
                                    <td><?php echo $resultado['activo'] ? '✅ Activo' : '❌ Inactivo'; ?></td>
                                    <td class="actions">
                                        <a href="?editar_resultado=<?php echo $resultado['id']; ?>" class="btn-edit">Editar</a>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="delete_resultado">
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
                
                <!-- ============================================
                TAB: TESTIMONIOS
                ============================================ -->
                <div id="tab-testimonios" class="tab-content">
                    <!-- Formulario Agregar/Editar Testimonio -->
                    <div class="admin-card">
                        <h2><?php echo $modo_edicion_testimonio ? '✏️ Editar Testimonio' : '➕ Agregar Testimonio'; ?></h2>
                        <form method="POST" class="admin-form" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="<?php echo $modo_edicion_testimonio ? 'edit_testimonio' : 'add_testimonio'; ?>">
                            <?php if ($modo_edicion_testimonio): ?>
                                <input type="hidden" name="id" value="<?php echo $testimonio_editar['id']; ?>">
                            <?php endif; ?>
                            
                            <div class="form-group">
                                <label>Nombre *</label>
                                <input type="text" name="nombre" required value="<?php echo $modo_edicion_testimonio ? htmlspecialchars($testimonio_editar['nombre']) : ''; ?>">
                            </div>
                            <div class="form-group">
                                <label>Testimonio *</label>
                                <textarea name="testimonio" required rows="4"><?php echo $modo_edicion_testimonio ? htmlspecialchars($testimonio_editar['testimonio']) : ''; ?></textarea>
                            </div>
                            
                            <div class="form-group upload-area">
                                <input type="file" name="foto" accept="image/*">
                                <div class="upload-icon"><i class="fas fa-user-circle"></i></div>
                                <div class="upload-text"><strong>Haz clic o arrastra</strong> una foto para subir</div>
                                <div class="upload-formats">Formatos: JPG, PNG, GIF, WebP | Máx: 2MB</div>
                                
                                <?php if ($modo_edicion_testimonio && !empty($testimonio_editar['foto'])): ?>
                                    <div class="upload-preview" id="previewFotoExistente">
                                        <img src="../<?php echo htmlspecialchars($testimonio_editar['foto']); ?>" alt="Foto actual">
                                        <button type="button" class="remove-img" onclick="eliminarFotoExistente()">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                    <input type="hidden" name="eliminar_foto" id="eliminar_foto" value="0">
                                <?php endif; ?>
                                
                                <div id="previewFotoNueva" style="display:none;" class="upload-preview">
                                    <img id="previewFotoImg" src="" alt="Vista previa">
                                    <button type="button" class="remove-img" onclick="removerFotoNuevaPreview()">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Orden</label>
                                <input type="number" name="orden" value="<?php echo $modo_edicion_testimonio ? $testimonio_editar['orden'] : '0'; ?>">
                                <small style="color:#999;">Número más bajo = aparece primero</small>
                            </div>
                            <div class="form-group">
                                <label>
                                    <input type="checkbox" name="activo" value="1" <?php echo ($modo_edicion_testimonio && $testimonio_editar['activo']) || !$modo_edicion_testimonio ? 'checked' : ''; ?>> 
                                    Activo
                                </label>
                            </div>
                            <button type="submit" class="btn-primary">
                                <?php echo $modo_edicion_testimonio ? 'Actualizar Testimonio' : 'Agregar Testimonio'; ?>
                            </button>
                            <?php if ($modo_edicion_testimonio): ?>
                                <a href="resultados.php" class="btn-secondary" style="display:inline-block;padding:12px 30px;background:var(--gris-claro);color:var(--azul-marino);border-radius:5px;text-decoration:none;font-weight:600;margin-left:10px;">Cancelar</a>
                            <?php endif; ?>
                        </form>
                    </div>
                    
                    <!-- Lista de Testimonios -->
                    <div class="admin-card">
                        <h2>Testimonios (<?php echo count($testimonios); ?>)</h2>
                        <table class="admin-table">
                            <thead>
                                <tr><th>#</th><th>Foto</th><th>Nombre</th><th>Testimonio</th><th>Orden</th><th>Estado</th><th>Acciones</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach($testimonios as $testimonio): ?>
                                <tr>
                                    <td><?php echo $testimonio['id']; ?></td>
                                    <td>
                                        <?php if (!empty($testimonio['foto'])): ?>
                                            <img src="../<?php echo htmlspecialchars($testimonio['foto']); ?>" class="testimonio-foto-mini">
                                        <?php else: ?>
                                            <i class="fas fa-user-circle" style="font-size:35px;color:#ccc;"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($testimonio['nombre']); ?></td>
                                    <td><?php echo htmlspecialchars(substr($testimonio['testimonio'], 0, 60)) . '...'; ?></td>
                                    <td><?php echo $testimonio['orden']; ?></td>
                                    <td><?php echo $testimonio['activo'] ? '✅ Activo' : '❌ Inactivo'; ?></td>
                                    <td class="actions">
                                        <a href="?editar_testimonio=<?php echo $testimonio['id']; ?>" class="btn-edit">Editar</a>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="delete_testimonio">
                                            <input type="hidden" name="id" value="<?php echo $testimonio['id']; ?>">
                                            <button type="submit" class="btn-delete" onclick="return confirm('¿Eliminar este testimonio?')">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        // ============================================
        // PESTAÑAS
        // ============================================
        function mostrarTab(tab) {
            // Ocultar todas las pestañas
            document.querySelectorAll('.tab-content').forEach(el => {
                el.classList.remove('active');
            });
            document.querySelectorAll('.tab-btn').forEach(el => {
                el.classList.remove('active');
            });
            
            // Mostrar la pestaña seleccionada
            document.getElementById('tab-' + tab).classList.add('active');
            
            // Activar el botón correspondiente
            document.querySelectorAll('.tab-btn').forEach(el => {
                if (el.textContent.trim().toLowerCase().includes(tab === 'casos' ? 'casos' : 'testimonios')) {
                    el.classList.add('active');
                }
            });
        }
        
        // ============================================
        // PREVIEW DE IMAGEN (CASOS)
        // ============================================
        const fileInput = document.querySelector('input[name="imagen"]');
        const previewNueva = document.getElementById('previewNueva');
        const previewImg = document.getElementById('previewImg');
        
        if (fileInput) {
            fileInput.addEventListener('change', function() {
                if (this.files.length > 0) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImg.src = e.target.result;
                        previewNueva.style.display = 'inline-block';
                        if (document.getElementById('previewExistente')) {
                            document.getElementById('previewExistente').style.display = 'none';
                        }
                    };
                    reader.readAsDataURL(this.files[0]);
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
        
        // ============================================
        // PREVIEW DE FOTO (TESTIMONIOS)
        // ============================================
        const fotoInput = document.querySelector('input[name="foto"]');
        const previewFotoNueva = document.getElementById('previewFotoNueva');
        const previewFotoImg = document.getElementById('previewFotoImg');
        
        if (fotoInput) {
            fotoInput.addEventListener('change', function() {
                if (this.files.length > 0) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewFotoImg.src = e.target.result;
                        previewFotoNueva.style.display = 'inline-block';
                        if (document.getElementById('previewFotoExistente')) {
                            document.getElementById('previewFotoExistente').style.display = 'none';
                        }
                    };
                    reader.readAsDataURL(this.files[0]);
                }
            });
        }
        
        function removerFotoNuevaPreview() {
            previewFotoNueva.style.display = 'none';
            previewFotoImg.src = '';
            fotoInput.value = '';
            if (document.getElementById('previewFotoExistente')) {
                document.getElementById('previewFotoExistente').style.display = 'inline-block';
            }
        }
        
        function eliminarFotoExistente() {
            if (confirm('¿Eliminar la foto actual?')) {
                document.getElementById('previewFotoExistente').style.display = 'none';
                document.getElementById('eliminar_foto').value = '1';
            }
        }
    </script>
</body>
</html>